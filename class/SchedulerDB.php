<?php

require_once 'util.php';
require_once 'Students.php';
require_once 'Teacher.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/RTSS/constant.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/RTSS/sms/send_sms.php';

class SchedulerDB
{

    private $date;
    private $weekday;
    private $date_str;
    private $leave_dict; //num of leave
    private $relief_dict; //num of relief
    private $on_leave_info;  //leave info
    private $teacher_list;   //accname - name/type match
    private $temp_list;
    private $lesson_list;
    private $teacher_lesson_list;
    private $teacher_class_list;

    public function __construct($date)
    {
        $this->date = $date;
        $this->weekday = $date->format('N') - 0;
        $this->date_str = $date->format('Y-m-d');
        $this->leave_dict = Array();
        $this->relief_dict = Array();
        $this->on_leave_info = Teacher::getTeacherOnLeave($this->date_str);
        $this->teacher_list = Teacher::getAllTeachers();
        $this->temp_list = Teacher::getTempTeacher($this->date_str);

        //query num_of_leave slot
        $sql_query_num_of_leave = "select teacher_id, sum(num_of_slot) as num_of_leave from rs_leave_info group by teacher_id";
        $db_con = Constant::connect_to_db('ntu');

        $query_num_of_leave_result = Constant::sql_execute($db_con, $sql_query_num_of_leave);
        if (is_null($query_num_of_leave_result))
        {
            throw new DBException("Fail to query number of leave information", __FILE__, __LINE__);
        }
        foreach($query_num_of_leave_result as $row)
        {
            $this->leave_dict[$row['teacher_id']] = $row['num_of_leave'];
        }

        //query num_of_relief slot
        $sql_query_num_of_relief = "select relief_teacher, sum(num_of_slot) as num_of_relief from rs_relief_info group by relief_teacher";
        $query_num_of_relief_result = Constant::sql_execute($db_con, $sql_query_num_of_relief);
        if (is_null($query_num_of_relief_result))
        {
            throw new DBException("Fail to query number of relief information:".mysql_error(), __FILE__, __LINE__);
        }
        foreach($query_num_of_relief_result as $row)
        {

            $this->relief_dict[$row['relief_teacher']] = $row['num_of_relief'];
        }

        //create lesson dictionary
        $this->lesson_list = Array();

        $sql_query_lessons = "select * from ct_lesson where weekday = ".$this->weekday.";";
        $lesson_query_result = Constant::sql_execute($db_con, $sql_query_lessons);

        if (is_null($lesson_query_result))
        {
            throw new DBException("Fail to query lesson from database", __FILE__, __LINE__);
        }

        foreach($lesson_query_result as $row)
        {
            $lesson_id = $row["lesson_id"];
            $start_time = $row["start_time"];

            $date_object = new DayTime($this->weekday, $start_time);
            $one_lesson = new Lesson($date_object, $row["subj_code"], $row["venue"]);

            $one_lesson->endTimeSlot = $row["end_time"];
            $one_lesson->lessonId = $row["lesson_id"];

            if (strcmp($row['type'], 'A') === 0)
            {
                $one_lesson->isMandatory = $row['highlighted'];
            }

            $this->lesson_list[$lesson_id] = $one_lesson;
        }

        //class of one lesson
        $sql_query_class = "SELECT ct_class_matching.* FROM ct_class_matching, ct_lesson WHERE ct_lesson.lesson_id = ct_class_matching.lesson_id
            AND ct_lesson.weekday = " . $this->weekday . ";";
        $class_query_result = Constant::sql_execute($db_con, $sql_query_class);

        if (is_null($class_query_result))
        {
            throw new DBException("Fail to query class from database", __FILE__, __LINE__);
        }

         foreach($class_query_result as $row)
        {
            $one_class = new Students($row['class_name']);
            $the_lesson = $this->lesson_list[$row['lesson_id']];
            $the_lesson->classes[] = $one_class;
        }

        //teacher-lesson match list
        $this->teacher_lesson_list = Array();

        $sql_query_teacher = "SELECT ct_teacher_matching.* FROM ct_teacher_matching, ct_lesson WHERE ct_lesson.lesson_id = ct_teacher_matching.lesson_id AND ct_lesson.weekday = " . $this->weekday . ";";
        $teacher_query_result = Constant::sql_execute($db_con, $sql_query_teacher);
        if (is_null($teacher_query_result))
        {
            throw new DBException("Fail to query teacher from database", __FILE__, __LINE__);
        }

        foreach($teacher_query_result as $row)
        {
            if (!array_key_exists($row['teacher_id'], $this->teacher_lesson_list))
            {
                $this->teacher_lesson_list[$row['teacher_id']] = Array();
            }

            $this->teacher_lesson_list[$row['teacher_id']][] = $row['lesson_id'];
        }

        //teacher-class match list
        $this->teacher_class_list = Array();

        $sql_query_teacher_class = "Select ct_teacher_matching.teacher_id as teacher, ct_class_matching.class_name as class from ct_lesson, ct_teacher_matching, ct_class_matching where ct_lesson.lesson_id = ct_teacher_matching.lesson_id and ct_lesson.lesson_id = ct_class_matching.lesson_id;";
        $query_teacher_class_result = Constant::sql_execute($db_con, $sql_query_teacher_class);
        if (is_null($query_teacher_class_result))
        {
            throw new DBException("Fail to query teacher-class from database", __FILE__, __LINE__);
        }

        foreach($query_teacher_class_result as $row)
        {
            if (!array_key_exists($row['teacher'], $this->teacher_class_list))
            {
                $this->teacher_class_list[$row['teacher']] = Array();
            }

            $this->teacher_class_list[$row['teacher']][$row['class']] = true;
        }
    }

    public function getLeave()
    {
        $result = Array(
            "Temp" => Array(),
            "Aed" => Array(),
            "Untrained" => Array(),
            "Normal" => Array(),
            "Hod" => Array()
        );

        //query leave
        foreach ($this->on_leave_info as $a_info)
        {
            $leave_time = SchedulerDB::trimTimePeriod($a_info['datetime'][0][0], $a_info['datetime'][1][0], $a_info['datetime'][0][1], $a_info['datetime'][1][1], $this->date_str, $a_info['leaveID']);

            $a_leave = Array(
                "startLeave" => $leave_time[0],
                "endLeave" => $leave_time[1]
            );

            $algo_type = Constant::$teacher_type[$a_info['type']];
            if (array_key_exists($a_info['accname'], $result[$algo_type]))
            {
                $result[$algo_type][$a_info['accname']][] = $a_leave;
            } else
            {
                $result[$algo_type][$a_info['accname']] = Array($a_leave);
            }
        }

        return $result;
    }

    public function getExcludedTeachers()
    {
        $result = Array(
            "Temp" => Array(),
            "Aed" => Array(),
            "Untrained" => Array(),
            "Normal" => Array(),
            "Hod" => Array()
        );

        $db_type = array_keys(Constant::$teacher_type);

        $all_excluded = Teacher::getExcludingList($this->date->format('Y/m/d'));

        foreach ($all_excluded as $teacher_id)
        {
            if (!empty($this->teacher_list[$teacher_id]))
            {
                $type = $this->teacher_list[$teacher_id]['type'];
            } else if (!empty($this->temp_list[$teacher_id]))
            {
                $type = $db_type[2];
            } else
            {
                $type = $db_type[0];
            }

            $result[Constant::$teacher_type[$type]][$teacher_id] = true;
        }

        return $result;
    }

    public function getNormalTeachers()
    {
        return $this->customizeTeacherList("normal");
    }

    public function getHodTeachers()
    {
        return $this->customizeTeacherList("HOD");
    }

    public function getUntrainedTeachers()
    {
        return $this->customizeTeacherList("untrained");
    }

    public function getAedTeachers()
    {
        $result = $this->customizeTeacherList("AED");

        $sql_query_speciality = "select * from ct_aed_speciality;";
        $db_con = Constant::connect_to_db('ntu');
        $query_speciality_result = Constant::sql_execute($db_con, $sql_query_speciality);
        if (is_null($query_speciality_result))
        {
            throw new DBException("Fail to query aed speciality", __FILE__, __LINE__);
        }

        foreach($query_speciality_result as $row)
        {
            if (array_key_exists($row['teacher_id'], $result))
            {
                $result[$row['teacher_id']]->speciality = $row['speciality'];
            }
        }

        return $result;
    }

    public function getTempTeachers()
    {
        $result_list = Array();

        foreach ($this->temp_list as $a_teacher)
        {
            if (array_key_exists($a_teacher['accname'], $result_list))
            {
                $the_teacher = $result_list[$a_teacher['accname']];
            } else
            {
                $the_teacher = new Teacher("dummy");

                $the_teacher->accname = $a_teacher['accname'];
                $the_teacher->name = $a_teacher['fullname'];

                if (array_key_exists($the_teacher->accname, $this->leave_dict))
                {
                    $the_teacher->noLessonMissed = $this->leave_dict[$the_teacher->accname];
                }
                if (array_key_exists($the_teacher->accname, $this->relief_dict))
                {
                    $the_teacher->noLessonRelived = $this->relief_dict[$the_teacher->accname];
                }

                $result_list[$the_teacher->accname] = $the_teacher;
            }

            $the_teacher->availability[] = SchedulerDB::trimTimePeriod($a_teacher['datetime'][0][0], $a_teacher['datetime'][1][0], $a_teacher['datetime'][0][1], $a_teacher['datetime'][1][1], $this->date_str, $a_teacher['availability_id']);
        }

        return $result_list;
    }

    private static function trimTimePeriod($start_date, $end_date, $start_time, $end_time, $query_date, $leave_id)
    {
        $start_date_obj = new DateTime($start_date);
        $end_date_obj = new DateTime($end_date);
        $query_date_obj = new DateTime($query_date);

        $start_diff = $start_date_obj->diff($query_date_obj);
        $end_diff = $end_date_obj->diff($query_date_obj);

        $start_time_index = SchoolTime::getTimeIndex($start_time);
        $end_time_index = SchoolTime::getTimeIndex($end_time);
        
        if ($start_diff->d !== 0 && $end_diff->d !== 0)
        {
            return array(1, 15, $leave_id);
        } else if ($start_diff->d === 0 && $end_diff->d !== 0)
        {
            if($start_time_index === -1)
            {
                throw new DBException("Error in time format", __FILE__, __LINE__);
            }
            
            return array($start_time_index, 15, $leave_id);
        } else if ($start_diff->d !== 0 && $end_diff->d === 0)
        {
            if($end_time_index === -1)
            {
                throw new DBException("Error in time format", __FILE__, __LINE__);
            }
            
            return array(1, $end_time_index, $leave_id);
        } else
        {
            if($start_time_index === -1 || $end_time_index === -1)
            {
                throw new DBException("Error in time format", __FILE__, __LINE__);
            }
            
            return array($start_time_index, $end_time_index, $leave_id);
        }
    }

    private function customizeTeacherList($type)
    {
        $teacher_dict = Array();
        $result = Teacher::getTeacherName($type);

        foreach ($result as $a_normal)
        {
            $temp_normal = new Teacher("dummy");
            //accname
            $temp_normal->accname = $a_normal["accname"];
            //name
            $temp_normal->name = $a_normal["fullname"];
            
            //noLessonMissed
            if (array_key_exists($a_normal["accname"], $this->leave_dict))
            {
                $temp_normal->noLessonMissed = $this->leave_dict[$a_normal["accname"]];
            }
            //noLessonRelived
            if (array_key_exists($a_normal["accname"], $this->relief_dict))
            {
                $temp_normal->noLessonRelived = $this->relief_dict[$a_normal["accname"]];
            }

            //timetable
            if (array_key_exists($a_normal['accname'], $this->teacher_lesson_list))
            {
                foreach ($this->teacher_lesson_list[$a_normal["accname"]] as $lesson_id)
                {
                    $the_lesson = $this->lesson_list[$lesson_id];

                    for ($i = $the_lesson->startTimeSlot; $i < $the_lesson->endTimeSlot; $i++)
                    {
                        if (!array_key_exists($i, $temp_normal->timetable))
                        {
                            $temp_normal->timetable[$i] = $the_lesson;
                        }
                    }
                }
            }

            //classes of teacher
            if (array_key_exists($a_normal["accname"], $this->teacher_class_list))
            {
                $temp_normal->classes = $this->teacher_class_list[$a_normal["accname"]];
            }

            $teacher_dict[$a_normal["accname"]] = $temp_normal;
        }

        return $teacher_dict;
    }

    public function getRecommendedNoOfLessons()
    {
        return 10;
    }

    static function setScheduleResult($scheduleResults, $date)
    {
        $db_con = Constant::connect_to_db("ntu");
        if(empty($db_con))
        {
            throw new DBException("Fail to insert schedule result", __FILE__, __LINE__);
        }
        
        //delete if there is any
        $sql_delete = "delete from temp_each_alternative;";
        $delete_result = Constant::sql_execute($db_con, $sql_delete);
        if(is_null($delete_result))
        {
            throw new DBException('Fail to clear temporary schedules', __FILE__, __LINE__);
        }
        
        //insert
        $sql_insert = "insert into temp_each_alternative values ";
        $has_value = false;
        foreach($scheduleResults as $id => $a_result)
        {
            $relief = $a_result['relievedLessons'];
            
            foreach($relief as $a_relief)
            {
                $has_value = true;
                $diff = $a_relief->endTimeSlot - $a_relief->startTimeSlot;
                $sql_insert .= "(".$id.",'".$a_relief->lessonId."','".$date."',".$a_relief->startTimeSlot.",".$a_relief->endTimeSlot.",'".$a_relief->teacherOriginal."','".$a_relief->teacherRelief."',NULL,".$diff."),";
            }
        }
        
        if($has_value)
        {
            $sql_insert = substr($sql_insert, 0, -1).';';
        
            $execute = Constant::sql_execute($db_con, $sql_insert);
            if(is_null($execute))
            {
                throw new DBException("Fail to insert scheduling result", __FILE__, __LINE__);
            }
        }
    }

    /**
     * time index 1-based
     * @param int $schedule_index [-1, max index - 1]; -1 -> return all results; >=0 -> return the specific table
     */
    public static function getScheduleResult($schedule_index = -1)
    {
        $result = Array();
        
        if($schedule_index < -1)
        {
            throw new DBException('Schedule_index shall not be less than -1', __FILE__, __LINE__);
        }
        
        $normal_dict = Teacher::getAllTeachers();
        $temp_dict = Teacher::getTempTeacher("");

        $db_con = Constant::connect_to_db("ntu");
        if(empty($db_con))
        {
            return $result;
        }
        
        if($schedule_index === -1)
        {
            $sql_schedule = "select * from (temp_each_alternative left join ct_class_matching on temp_each_alternative.lesson_id = ct_class_matching.lesson_id) order by start_time, end_time ASC;";
        }
        else
        {
            $sql_schedule = "select * from (temp_each_alternative left join ct_class_matching on temp_each_alternative.lesson_id = ct_class_matching.lesson_id) where temp_each_alternative.schedule_id = ".$schedule_index." order by start_time, end_time ASC;;";
        }
        
        $schedule_result = Constant::sql_execute($db_con, $sql_schedule);
        if(is_null($schedule_result))
        {
            return $result;
        }

        foreach($schedule_result as $row)
        {
            $schedule_id = $row['schedule_id'];

            if(!array_key_exists($schedule_id, $result))
            {
                $result[$schedule_id] = Array();
            }

            $relief_alr_created = false;
            for($i = 0; $i < count($result[$schedule_id]); $i++)
            {
                if(empty($result[$schedule_id][$i]))
                {
                    continue;
                }

                if(strcmp($result[$schedule_id][$i]['id'], $row['lesson_id']) === 0)
                {
                    $relief_alr_created = true;
                    if(!empty($row['class_name']))
                    {
                        $result[$schedule_id][$i]['class'][] = $row['class_name'];
                    }
                    break;
                }
            }

            if(!$relief_alr_created)
            {
                $leave_acc = $row['leave_teacher'];
                $relief_acc =$row['relief_teacher'];

                if(array_key_exists($leave_acc, $normal_dict))
                {
                    $leave_full = $normal_dict[$leave_acc]['name'];
                }
                else
                {
                    $leave_full = "";
                }

                if(array_key_exists($relief_acc, $normal_dict))
                {
                    $relief_full = $normal_dict[$relief_acc]['name'];
                }
                else if(array_key_exists($relief_acc, $temp_dict))
                {
                    $relief_full = $temp_dict[$relief_acc]['fullname'];
                }
                else
                {
                    $relief_full = "";
                }

                $temp = Array(
                    "class" => Array(),
                    "id" => $row['lesson_id'],
                    "time" => Array($row['start_time'] - 0, $row['end_time'] - 0),
                    "teacherAccName" => $leave_acc,
                    "reliefAccName" => $relief_acc,
                    "teacherOnLeave" => $leave_full,
                    "reliefTeacher" =>$relief_full
                );

                if(!empty($row['class_name']))
                {
                    $temp["class"][] = $row['class_name'];
                }

                $result[$schedule_id][] = $temp;
            }
        }
        
        return $result;
    }
    
    public static function override($schedule_index, $lesson_id, $accname_old, $accname_new)
    {
        $db_con = Constant::connect_to_db("ntu");
        if(empty($db_con))
        {
            return false;
        }
        
        $lesson_id = mysql_real_escape_string(trim($lesson_id));
        $accname_old = mysql_real_escape_string(trim($accname_old));
        $accname_new = mysql_real_escape_string(trim($accname_new));
        
        $sql_update = "update temp_each_alternative set relief_teacher = '".$accname_new."' where schedule_id = ".$schedule_index." and lesson_id = '".$lesson_id."' and leave_teacher = '".$accname_old."';";
        $update_result = Constant::sql_execute($db_con, $sql_update);
        if(is_null($update_result))
        {
            return false;
        }
        
        return true;
    }
    
    public static function approve($schedule_index, $date)
    {
        //1. move from temp to relief_info and delete temp
        $db_con = Constant::connect_to_db("ntu");
        if(empty($db_con))
        {
            throw new DBException('Fail to approve the schedule', __FILE__, __LINE__);
        }
        
        //override for the particular day
        $sql_clear = "delete from rs_relief_info where date = DATE('".$date."');";
        $clear_result = Constant::sql_execute($db_con, $sql_clear);
        if(is_null($clear_result))
        {
            throw new DBException('Fail to clear exist relief record', __FILE__, __LINE__);
        }
        
        //copy selected one
        $sql_insert_select = "insert into rs_relief_info (select lesson_id, start_time, end_time, date, leave_teacher, relief_teacher, leave_id_ref, num_of_slot from temp_each_alternative where schedule_id = ".$schedule_index.");";
        $insert_result = Constant::sql_execute($db_con, $sql_insert_select);
        if(is_null($insert_result))
        {
            throw new DBException('Fail to approve the schedule', __FILE__, __LINE__);
        }
        
        //delete temp
        $sql_delete = "delete from temp_each_alternative;";
        $delete_result = Constant::sql_execute($db_con, $sql_delete);
        if(is_null($delete_result))
        {
            throw new DBException('Fail to clear temporary schedules', __FILE__, __LINE__);
        }
        
        //2. construct content
        $sql_selected = "select rs_relief_info.lesson_id, start_time_index, end_time_index, relief_teacher, subj_code, venue, class_name from ((rs_relief_info left join ct_lesson on rs_relief_info.lesson_id = ct_lesson.lesson_id) left join ct_class_matching on ct_lesson.lesson_id = ct_class_matching.lesson_id) where rs_relief_info.date = DATE('".$date."');";
        $selected_result = Constant::sql_execute($db_con, $sql_selected);
        if(is_null($selected_result))
        {
            throw new DBException('Fail to clear temporary schedules', __FILE__, __LINE__);
        }
        
        //a list of relief teacher, with their relief duties
        //{accname => {unique_relief_key=>{...}, ...}, ...}
        $list = Array();
        foreach($selected_result as $row)
        {
            $accname = $row['relief_teacher'];
            
            if(!array_key_exists($accname, $list))
            {
                $list[$accname] = Array();
            }
            
            $unique_relief_key = $row['lesson_id'].$row['start_time_index'].$row['end_time_index'];
            if(array_key_exists($unique_relief_key, $list[$accname]))
            {
                if(!empty($row['class_name']))
                {
                    $list[$accname][$unique_relief_key]['class'][] = $row['class_name'];
                }
            }
            else
            {
                $venue = empty($row['venue'])?"":$row['venue'];
                $subject = empty($row['subj_code'])?"":$row['subj_code'];
                
                $one_relief = Array(
                    "start_time" => $row['start_time_index'] - 0,
                    "end_time" => $row['end_time_index'] - 0,
                    "subject" => $subject,
                    "venue" => $venue,
                    "class" => Array()
                );
                
                if(!empty($row['class_name']))
                {
                    $one_relief['class'][] = $row['class_name'];
                }
                
                $list[$accname][$unique_relief_key] = $one_relief;
            }
        }
        
        //3. inform all teachers (Teacher::getTeacherContact)
        $teacher_list = Teacher::getTeacherContact();
        
        $sms_input = Array();
        foreach($list as $key=>$one)
        {
            $accname = $key;
            
            $phone = "";
            $email = "";
            $name = "";
            
            if(array_key_exists($accname, $teacher_list))
            {
                $phone = $teacher_list[$accname]['phone'];
                $email = $teacher_list[$accname]['email'];
                $name = $teacher_list[$accname]['name'];
            }
            
            $message = "";
            
            $index = 1;
            foreach($one as $a_relief)
            {
                $start_time = SchoolTime::getTimeValue($a_relief['start_time']);
                $end_time = SchoolTime::getTimeValue($a_relief['end_time']);
                
                $classes = implode(",", $a_relief['class']);
                $subject = $a_relief['subject'];
                $venue = empty($a_relief['venue'])?"in classroom":$a_relief['venue'];
                
                $message .= "|    $index : On $date $start_time-$end_time take relief for $classes subject-$subject venue-$venue  |";
                
                $index++;
            }
            
            $one_teacher = Array(
                "phoneNum" => $phone,
                "name" => $name,
                "accName" => $accname,
                "message" => $message
            );
            
            $sms_input[] = $one_teacher;
        }
        
        sendSMS($sms_input, $date);
    }
    
    public static function allSchduleIndex()
    {
        $db_con = Constant::connect_to_db("ntu");
        if(empty($db_con))
        {
            throw new DBException('Fail to query schedule index', __FILE__, __LINE__);
        }
        
        $sql_index = "select distinct schedule_id from temp_each_alternative;";
        $index_result = Constant::sql_execute($db_con, $sql_index);
        if(is_null($index_result))
        {
            throw new DBException('Fail to query schedule index', __FILE__, __LINE__);
        }
        
        $result = Array();
        foreach($index_result as $row)
        {
            $result[] = $row['schedule_id'] - 0;
        }
        
        return $result;
    }
    
    /*
    public static function scheduleResultNum()
    {
        
        $db_con = Constant::connect_to_db("ntu");
        if(empty($db_con))
        {
            throw new DBException('Fail to query number of schedule', __FILE__, __LINE__);
        }
        
        $sql_query_num = "select count(*) as num from temp_all_results;";
        $result = Constant::sql_execute($db_con, $sql_query_num);

        if(empty($result) || count($result) === 0)
        {
            return 0;
        }

        return $result[0]['num'];
         
    }
     * 
     */
}

?>
