<?php

spl_autoload_register(function($class){
    require_once "$class.php";
});

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
        $db_con = Constant::connect_to_db('ntu');

        if (empty($db_con))
        {
            throw new DBException("Fail to query algo input", __FILE__, __LINE__);
        }

        //query sem info by date
        $sql_query_sem = "select * from ct_semester_info where date('$this->date_str') between start_date and end_date;";
        $query_sem_result = Constant::sql_execute($db_con, $sql_query_sem);
        if(is_null($query_sem_result))
        {
            throw new DBException('Fail to query algorithm input', __FILE__, __LINE__);
        }

        if(empty($query_sem_result))
        {
            throw new DBException('Lesson info not available on '.$this->date_str, __FILE__, __LINE__, 1);
        }

        $timetable_id = $query_sem_result[0]['sem_id'];
        $year = $query_sem_result[0]['year'];
        $sem = $query_sem_result[0]['sem_num'];

        //query number of leave and relief
        $overall_report = Teacher::overallReport("", "fullname", SORT_ASC, $year, $sem);
        foreach($overall_report as $row)
        {
            $this->leave_dict[$row['accname']] = $row['numOfMC'];
            $this->relief_dict[$row['accname']] = $row['numOfRelief'];
        }

        //create lesson dictionary
        $this->lesson_list = Array();

        $sql_query_lessons = "select * from ct_lesson where weekday = $this->weekday and sem_id = $timetable_id;";
        $lesson_query_result = Constant::sql_execute($db_con, $sql_query_lessons);

        if (is_null($lesson_query_result))
        {
            throw new DBException("Fail to query lesson from database", __FILE__, __LINE__, 2);
        }

        foreach ($lesson_query_result as $row)
        {
            $lesson_id = $row["lesson_id"];
            $start_time = $row["start_time_index"];

            $date_object = new DayTime($this->weekday, $start_time);
            $one_lesson = new Lesson($date_object, $row["subj_code"], $row["venue"]);

            $one_lesson->endTimeSlot = $row["end_time_index"];
            $one_lesson->lessonId = $row["lesson_id"];

            if (strcmp($row['type'], 'A') === 0)
            {
                $one_lesson->isMandatory = $row['highlighted'];
            }

            $this->lesson_list[$lesson_id] = $one_lesson;
        }

        //class of lesson
        $sql_query_class = "SELECT ct_class_matching.* FROM ct_class_matching, ct_lesson WHERE ct_lesson.lesson_id = ct_class_matching.lesson_id
            AND ct_lesson.weekday = $this->weekday and ct_lesson.sem_id = $timetable_id;";
        $class_query_result = Constant::sql_execute($db_con, $sql_query_class);

        if (is_null($class_query_result))
        {
            throw new DBException("Fail to query class from database", __FILE__, __LINE__, 2);
        }

        foreach ($class_query_result as $row)
        {
            $one_class = new Students($row['class_name']);
            if (!array_key_exists($row['lesson_id'], $this->lesson_list))
            {
                continue;
            }
            $the_lesson = $this->lesson_list[$row['lesson_id']];
            $the_lesson->classes[] = $one_class;
        }

        //teacher-lesson match list
        $this->teacher_lesson_list = Array();

        $sql_query_teacher = "SELECT ct_teacher_matching.* FROM ct_teacher_matching, ct_lesson WHERE ct_lesson.lesson_id = ct_teacher_matching.lesson_id AND ct_lesson.weekday = $this->weekday and ct_lesson.sem_id = $timetable_id;";
        $teacher_query_result = Constant::sql_execute($db_con, $sql_query_teacher);
        if (is_null($teacher_query_result))
        {
            throw new DBException("Fail to query teacher from database", __FILE__, __LINE__, 2);
        }

        foreach ($teacher_query_result as $row)
        {
            if (!array_key_exists($row['teacher_id'], $this->teacher_lesson_list))
            {
                $this->teacher_lesson_list[$row['teacher_id']] = Array();
            }

            $this->teacher_lesson_list[$row['teacher_id']][] = $row['lesson_id'];
        }

        //teacher-class match list
        $this->teacher_class_list = Array();

        $sql_query_teacher_class = "Select ct_teacher_matching.teacher_id as teacher, ct_class_matching.class_name as class from ct_lesson, ct_teacher_matching, ct_class_matching where ct_lesson.lesson_id = ct_teacher_matching.lesson_id and ct_lesson.lesson_id = ct_class_matching.lesson_id and ct_lesson.sem_id = $timetable_id;";
        $query_teacher_class_result = Constant::sql_execute($db_con, $sql_query_teacher_class);
        if (is_null($query_teacher_class_result))
        {
            throw new DBException("Fail to query teacher-class from database", __FILE__, __LINE__, 2);
        }

        foreach ($query_teacher_class_result as $row)
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

//            $a_leave = Array(
//                "startLeave" => $leave_time[0],
//                "endLeave" => $leave_time[1],
//                "leaveID" => $a_info['leaveID']
//            );
            $a_leave = Array(
                "startLeave" => $leave_time[0],
                "endLeave" => $leave_time[1]
            );


            $algo_type = Constant::$teacher_type[$a_info['type']];
            if (!array_key_exists($algo_type, $result))
            {
                continue;
            }
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

    public function getLeaveIds()
    {
        $leaveId = array();
        foreach ($this->on_leave_info as $a_info)
        {
            $leaveId[] = $a_info['leaveID'];
        }
        return $leaveId;
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
            throw new DBException("Fail to query aed speciality", __FILE__, __LINE__, 2);
        }

        foreach ($query_speciality_result as $row)
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

    public static function trimTimePeriod($start_date, $end_date, $start_time, $end_time, $query_date, $leave_id)
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
            if ($start_time_index === -1)
            {
                throw new DBException("Error in time format", __FILE__, __LINE__, 3);
            }

            return array($start_time_index, 15, $leave_id);
        } else if ($start_diff->d !== 0 && $end_diff->d === 0)
        {
            if ($end_time_index === -1)
            {
                throw new DBException("Error in time format", __FILE__, __LINE__, 3);
            }

            return array(1, $end_time_index, $leave_id);
        } else
        {
            if ($start_time_index === -1 || $end_time_index === -1)
            {
                throw new DBException("Error in time format", __FILE__, __LINE__, 3);
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
        $db_con = Constant::connect_to_db('ntu');
        if(empty($db_con))
        {
            throw new DBException('Fail to query recommended lesson', __FILE__, __LINE__);
        }

        $sql_query = "select value from admin_config where identifier = 'recommended_num';";
        $query_result = Constant::sql_execute($db_con, $sql_query);
        if(empty($query_result))
        {
            throw new DBException('Fail to query recommended lesson', __FILE__, __LINE__);
        }

        $num = $query_result[0]['value'] - 0;

        return $num;
    }

    static function setScheduleResult($date, $scheduleResults)
    {
        $db_con = Constant::connect_to_db("ntu");
        if (empty($db_con))
        {
            throw new DBException("Fail to insert schedule result", __FILE__, __LINE__);
        }

        //delete if there is any
        $sql_delete = "delete from temp_each_alternative;";
        $delete_result = Constant::sql_execute($db_con, $sql_delete);
        if (is_null($delete_result))
        {
            throw new DBException('Fail to clear temporary schedules', __FILE__, __LINE__, 2);
        }

        $sql_delete_skip = "delete from temp_aed_skip_info;";
        $delete_skip_result = Constant::sql_execute($db_con, $sql_delete_skip);
        if (is_null($delete_skip_result))
        {
            throw new DBException('Fail to clear temporary schedules', __FILE__, __LINE__, 2);
        }

        //insert relief into temp
        $sql_insert = "insert into temp_each_alternative (schedule_id, lesson_id, schedule_date, start_time_index, end_time_index, leave_teacher, relief_teacher, num_of_slot) values ";
        $sql_skip = "insert into temp_aed_skip_info (schedule_id, lesson_id, schedule_date, start_time_index, end_time_index, accname) values ";

        $has_value = false;
        $has_skip = false;

        foreach ($scheduleResults as $id => $a_result)
        {
            $relief = $a_result['relievedLessons'];

            foreach ($relief as $a_relief)
            {
                $has_value = true;
                $diff = $a_relief->endTimeSlot - $a_relief->startTimeSlot;
                $sql_insert .= "($id, '$a_relief->lessonId', '$date', $a_relief->startTimeSlot, $a_relief->endTimeSlot, '$a_relief->teacherOriginal', '$a_relief->teacherRelief', $diff),";
            }

            $skip = $a_result['skippedLessons'];

            foreach ($skip as $a_skip)
            {
                $has_skip = true;
                $end_time = $a_skip->startTimeSlot + 1;
                $sql_skip .= "($id, '$a_skip->lessonId', '$date', $a_skip->startTimeSlot, $end_time, '$a_skip->teacherOriginal'),";
            }
        }

        if ($has_value)
        {
            $sql_insert = substr($sql_insert, 0, -1) . ';';

            $execute = Constant::sql_execute($db_con, $sql_insert);
            if (is_null($execute))
            {
                throw new DBException("Fail to insert scheduling result", __FILE__, __LINE__, 2);
            }
        }

        if ($has_skip)
        {
            $sql_skip = substr($sql_skip, 0, -1) . ';';

            $execute = Constant::sql_execute($db_con, $sql_skip);
            if (is_null($execute))
            {
                throw new DBException("Fail to insert scheduling result", __FILE__, __LINE__, 2);
            }
        }    
    }

    /**
     * get temp schedule that are not approved. time index 1-based
     * @param int $schedule_index [-1, max index - 1]; -1 -> return all results; >=0 -> return the specific table
     */
    public static function getScheduleResult($schedule_index = -1)
    {
        $result = Array();

        if ($schedule_index < -1)
        {
            throw new DBException('Schedule_index shall not be less than -1', __FILE__, __LINE__, 3);
        }

        $normal_dict = Teacher::getAllTeachers();
        $temp_dict = Teacher::getTempTeacher("");

        $db_con = Constant::connect_to_db("ntu");
        if (empty($db_con))
        {
            throw new DBException('Fail to query schedule result', __FILE__, __LINE__);
        }

        if ($schedule_index === -1)
        {
            $sql_schedule = "select * from (temp_each_alternative left join ct_class_matching on temp_each_alternative.lesson_id = ct_class_matching.lesson_id) order by start_time_index, end_time_index ASC;";
        } else
        {
            $sql_schedule = "select * from (temp_each_alternative left join ct_class_matching on temp_each_alternative.lesson_id = ct_class_matching.lesson_id) where temp_each_alternative.schedule_id = " . $schedule_index . " order by start_time_index, end_time_index ASC;";
        }

        $schedule_result = Constant::sql_execute($db_con, $sql_schedule);
        if (is_null($schedule_result))
        {
            throw new DBException('Fail to query schedule result', __FILE__, __LINE__, 2);
        }

        foreach ($schedule_result as $row)
        {
            $schedule_id = $row['schedule_id'];

            if (!array_key_exists($schedule_id, $result))
            {
                $result[$schedule_id] = Array();
            }

            $relief_alr_created = false;
            for ($i = 0; $i < count($result[$schedule_id]); $i++)
            {
                if (empty($result[$schedule_id][$i]))
                {
                    continue;
                }

                if (strcmp($result[$schedule_id][$i]['id'], $row['lesson_id']) === 0 && strcmp($result[$schedule_id][$i]['reliefAccName'], $row['relief_teacher']) === 0 && $result[$schedule_id][$i]['time'][0] == $row['start_time_index'] && $result[$schedule_id][$i]['time'][1] == $row['end_time_index'])
                {
                    $relief_alr_created = true;
                    if (!empty($row['class_name']))
                    {
                        $result[$schedule_id][$i]['class'][] = $row['class_name'];
                    }
                    break;
                }
            }

            if (!$relief_alr_created)
            {
                $leave_acc = $row['leave_teacher'];
                $relief_acc = $row['relief_teacher'];

                if (array_key_exists($leave_acc, $normal_dict))
                {
                    $leave_full = $normal_dict[$leave_acc]['name'];
                } else
                {
                    $leave_full = "";
                }

                if (array_key_exists($relief_acc, $normal_dict))
                {
                    $relief_full = $normal_dict[$relief_acc]['name'];
                } else if (array_key_exists($relief_acc, $temp_dict))
                {
                    $relief_full = $temp_dict[$relief_acc]['fullname'];
                } else
                {
                    $relief_full = "";
                }

                $temp = Array(
                    "class" => Array(),
                    "id" => $row['lesson_id'],
                    "time" => Array($row['start_time_index'] - 0, $row['end_time_index'] - 0),
                    "teacherAccName" => $leave_acc,
                    "reliefAccName" => $relief_acc,
                    "teacherOnLeave" => $leave_full,
                    "reliefTeacher" => $relief_full,
                    "reliefID" => $row['temp_relief_id']
                );

                if (!empty($row['class_name']))
                {
                    $temp["class"][] = $row['class_name'];
                }

                $result[$schedule_id][] = $temp;
            }
        }

        return $result;
    }

    public static function overrideSet($state, $scheduleIndex)
    {
        $db_con = Constant::connect_to_db('ntu');
        if(empty($db_con))
        {
            throw new DBException("fail to set override", __FILE__, __LINE__);
        }
        
        if(strcmp($state, "start") === 0)
        {
            $sql_copy = "select * from temp_each_alternative where schedule_id = $scheduleIndex;";
            $copy = Constant::sql_execute($db_con, $sql_copy);
            if(is_null($copy))
            {
                throw new DBException("fail to set override".$sql_copy, __FILE__, __LINE__);
            }
            
            if(count($copy) === 0)
            {
                return;
            }
            
            $sql_insert_temp = "insert into temp_each_alternative (schedule_id, lesson_id, schedule_date, start_time_index, end_time_index, leave_teacher, relief_teacher, leave_id_ref, num_of_slot) values ";
            foreach($copy as $row)
            {
                $relief_id_ref = $row['temp_relief_id'];
                $lesson_id = $row['lesson_id'];
                $date = $row['schedule_date'];
                $start_time = $row['start_time_index'];
                $end_time = $row['end_time_index'];
                $leave_teacher = $row['leave_teacher'];
                $relief_teacher = $row['relief_teacher'];
                $num_of_slot = $row['num_of_slot'];
                
                $sql_insert_temp .= "(-1, '$lesson_id', '$date', $start_time, $end_time, '$leave_teacher', '$relief_teacher',$relief_id_ref ,$num_of_slot),";
            }
            $sql_insert_temp = substr($sql_insert_temp, 0, -1).';';
            
            $insert_temp = Constant::sql_execute($db_con, $sql_insert_temp);
            if(is_null($insert_temp))
            {
                throw new DBException("fail to set override", __FILE__, __LINE__);
            }
            
            //skip
            $sql_copy_skip = "select * from temp_aed_skip_info where schedule_id = $scheduleIndex;";
            $copy_skip = Constant::sql_execute($db_con, $sql_copy_skip);
            if(is_null($copy_skip))
            {
                throw new DBException("fail to set override", __FILE__, __LINE__);
            }
            
            if(count($copy_skip) > 0)
            {
                $sql_insert_temp_skip = "insert into temp_aed_skip_info (schedule_id, lesson_id, schedule_date, start_time_index, end_time_index, accname) values ";
                foreach($copy_skip as $row)
                {
                    $lesson_id = $row['lesson_id'];
                    $date = $row['schedule_date'];
                    $start_time = $row['start_time_index'];
                    $end_time = $row['end_time_index'];
                    $accname = $row['accname'];

                    $sql_insert_temp_skip .= "(-1, '$lesson_id', '$date', $start_time, $end_time, '$accname'),";
                }
                $sql_insert_temp_skip = substr($sql_insert_temp_skip, 0, -1).';';

                $insert_temp_skip = Constant::sql_execute($db_con, $sql_insert_temp_skip);
                if(is_null($insert_temp_skip))
                {
                    throw new DBException("fail to set override", __FILE__, __LINE__);
                }
            }
        }
        else if(strcmp($state, "cancel") === 0)
        {
            $sql_delete = "delete from temp_each_alternative where schedule_id = -1;";
            $delete = Constant::sql_execute($db_con, $sql_delete);
            if(is_null($delete))
            {
                throw new DBException("fail to cancel override", __FILE__, __LINE__);
            }
            
            $sql_delete_skip = "delete from temp_aed_skip_info where schedule_id = -1;";
            $delete_skip = Constant::sql_execute($db_con, $sql_delete_skip);
            if(is_null($delete_skip))
            {
                throw new DBException("fail to cancel override", __FILE__, __LINE__);
            }
        }
        else
        {
            //clear old
            $sql_delete_old = "delete from temp_each_alternative where schedule_id = $scheduleIndex;";
            $delete_old = Constant::sql_execute($db_con, $sql_delete_old);
            if(is_null($delete_old))
            {
                throw new DBException("fail to cancel override", __FILE__, __LINE__);
            }
            
            $sql_delete_skip_old = "delete from temp_aed_skip_info where schedule_id = $scheduleIndex;";
            $delete_skip_old = Constant::sql_execute($db_con, $sql_delete_skip_old);
            if(is_null($delete_skip_old))
            {
                throw new DBException("fail to cancel override", __FILE__, __LINE__);
            }
            
            //copy new 
            $sql_copy = "select * from temp_each_alternative where schedule_id = -1;";
            $copy = Constant::sql_execute($db_con, $sql_copy);
            if(is_null($copy))
            {
                throw new DBException("fail to set override", __FILE__, __LINE__);
            }
            if(count($copy) > 0)
            {
                $sql_insert_temp = "insert into temp_each_alternative (schedule_id, lesson_id, schedule_date, start_time_index, end_time_index, leave_teacher, relief_teacher, num_of_slot) values ";
                foreach($copy as $row)
                {
                    $lesson_id = $row['lesson_id'];
                    $date = $row['schedule_date'];
                    $start_time = $row['start_time_index'];
                    $end_time = $row['end_time_index'];
                    $leave_teacher = $row['leave_teacher'];
                    $relief_teacher = $row['relief_teacher'];
                    $num_of_slot = $row['num_of_slot'];

                    $sql_insert_temp .= "($scheduleIndex, '$lesson_id', '$date', $start_time, $end_time, '$leave_teacher', '$relief_teacher', $num_of_slot),";
                }
                $sql_insert_temp = substr($sql_insert_temp, 0, -1).';';

                $insert_temp = Constant::sql_execute($db_con, $sql_insert_temp);
                if(is_null($insert_temp))
                {
                    throw new DBException("fail to set override", __FILE__, __LINE__);
                }
            }
            
            $sql_copy_skip = "select * from temp_aed_skip_info where schedule_id = -1;";
            $copy_skip = Constant::sql_execute($db_con, $sql_copy_skip);
            if(is_null($copy_skip))
            {
                throw new DBException("fail to set override", __FILE__, __LINE__);
            }
            if(count($copy_skip) > 0)
            {
                $sql_insert_temp_skip = "insert into temp_aed_skip_info (schedule_id, lesson_id, schedule_date, start_time_index, end_time_index, accname) values ";
                foreach($copy_skip as $row)
                {
                    $lesson_id = $row['lesson_id'];
                    $date = $row['schedule_date'];
                    $start_time = $row['start_time_index'];
                    $end_time = $row['end_time_index'];
                    $accname = $row['accname'];

                    $sql_insert_temp_skip .= "($scheduleIndex, '$lesson_id', '$date', $start_time, $end_time, '$accname'),";
                }
                $sql_insert_temp_skip = substr($sql_insert_temp_skip, 0, -1).';';

                $insert_temp_skip = Constant::sql_execute($db_con, $sql_insert_temp_skip);
                if(is_null($insert_temp_skip))
                {
                    throw new DBException("fail to set override", __FILE__, __LINE__);
                }
            }
            //clear temp
            $sql_delete = "delete from temp_each_alternative where schedule_id = -1;";
            $delete = Constant::sql_execute($db_con, $sql_delete);
            if(is_null($delete))
            {
                throw new DBException("fail to cancel override", __FILE__, __LINE__);
            }
            
            $sql_delete_skip = "delete from temp_aed_skip_info where schedule_id = -1;";
            $delete_skip = Constant::sql_execute($db_con, $sql_delete_skip);
            if(is_null($delete_skip))
            {
                throw new DBException("fail to cancel override", __FILE__, __LINE__);
            }
        }
    }
    
    public static function override($temp_relief_id, $accname_new)
    {
        $aed_list = Teacher::getTeacherInfo('AED');

        $db_con = Constant::connect_to_db("ntu");
        if (empty($db_con))
        {
            return false;
        }

        $accname_new = mysql_real_escape_string(trim($accname_new));

        //1. retrieve old relief
        $sql_old_relief = "select * from temp_each_alternative where schedule_id = -1 and leave_id_ref = $temp_relief_id;";
        $old_relief_result = Constant::sql_execute($db_con, $sql_old_relief);
        if(empty($old_relief_result))
        {
            return false;
        }

        $old_relief = $old_relief_result[0];

        $relief_id = $old_relief["temp_relief_id"];
        $old_relief_teacher = $old_relief['relief_teacher'];
        $start_time_index = $old_relief['start_time_index'];
        $end_time_index = $old_relief['end_time_index'];
        $schedule_date = $old_relief['schedule_date'];

        // 1.1. see whether teachers are AED
        $old_aed = false;
        $new_aed = false;

        if(array_key_exists($old_relief_teacher, $aed_list))
        {
            $old_aed = true;
        }
        if(array_key_exists($accname_new, $aed_list))
        {
            $new_aed = true;
        }

        //update
        $sql_update = "update temp_each_alternative set relief_teacher = '" . $accname_new . "' where schedule_id = -1 and temp_relief_id = " . $relief_id . ";";
        $update_result = Constant::sql_execute($db_con, $sql_update);
        if (is_null($update_result))
        {
            //return false;
            throw new DBException("update", __FILE__, __LINE__);
        }

        //clear old aed
        if($old_aed)
        {
            //2. search skip of old relief
            // 2.1. - search all relief
            $sql_all_relief = "select start_time_index, end_time_index from temp_each_alternative where schedule_date = DATE('$schedule_date') and relief_teacher = '$old_relief_teacher' and schedule_id = -1;";
            $all_relief_result = Constant::sql_execute($db_con, $sql_all_relief);
            if(is_null($all_relief_result))
            {
                throw new DBException('Fail to query all relief duties', __FILE__, __LINE__, 2);
            }

            $have_class_index = array();  //array of start time index of releif duties
            foreach($all_relief_result as $row)
            {
                $start_time = $row['start_time_index'];
                $end_time = $row['end_time_index'];

                for($i = $start_time; $i < $end_time; $i++)
                {
                    $have_class_index[] = $i;
                }
            }

            // 2.2. - search all rs_aed_skip
            $sql_all_skip = "select * from temp_aed_skip_info where schedule_date = DATE('$schedule_date') and accname = '$old_relief_teacher' and schedule_id = -1;";
            $all_skip_result = Constant::sql_execute($db_con, $sql_all_skip);
            if(is_null($all_skip_result))
            {
                throw new DBException('Fail to query all skipped lessons', __FILE__, __LINE__, 2);
            }

            $skip_array = array();
            foreach($all_skip_result as $row)
            {
                $skip_array[$row['start_time_index']] = $row['temp_skip_id'];
            }

            // 2.3. - find skip ids to be recovered
            $diff = $end_time_index - $start_time_index;
            $recover_list = array();    //skip ids to be deleted

            for($i = $start_time_index; $i < $end_time_index; $i++)
            {
                if(!empty($skip_array[$i]))
                {
                    $recover_list[] = $skip_array[$i];
                }
            }

            foreach ($skip_array as $start=>$id)
            {
                if(count($recover_list) >= $diff)
                {
                    break;
                }

                if(!in_array($start, $have_class_index))
                {
                    $recover_list[] = $id;
                }
            }
            
             //delete old old skip
            if(count($recover_list) > 0)
            {
                $sql_delete_skip = "delete from temp_aed_skip_info where temp_skip_id in (".implode(",", $recover_list).");";
                $delete_skip_result = Constant::sql_execute($db_con, $sql_delete_skip);
                if(is_null($delete_skip_result))
                {
                    throw new DBException('Fail to cancel teh relief', __FILE__, __LINE__, 2);
                }
            }
        }

        //search lesson during relief of new accname; if there's mandatory, return false, else if there is optional, put to skip_array;
        if($new_aed)
        {
            $schedule_date_obj = new DateTime($schedule_date);
            $weekday = $schedule_date_obj->format('N');

            $sql_select_lesson = "select ct_lesson.* from ct_lesson, ct_teacher_matching where ct_lesson.lesson_id = ct_teacher_matching.lesson_id and ct_teacher_matching.teacher_id = '$accname_new' and ct_lesson.weekday = $weekday and !ct_lesson.highlighted;";
            $select_lesson_result = Constant::sql_execute($db_con, $sql_select_lesson);
            if(is_null($select_lesson_result))
            {
                throw new DBException("override", __FILE__, __LINE__);
            }

            $optional_lessons = array();
            foreach($select_lesson_result as $row)
            {
                $opt_start = $row['start_time_index'];
                $opt_end = $row['end_time_index'];

                for($i = $opt_start; $i < $opt_end; $i++)
                {
                    $optional_lessons[$i] = $row['lesson_id'];
                }
            }

            $new_skip = array();
            for($i = $start_time_index; $i < $end_time_index; $i++)
            {
                if(array_key_exists($i, $optional_lessons))
                {
                    $new_skip[] = array(
                        "start_time" => $i,
                        "lesson_id" => $optional_lessons[$i]
                    );
                }
            }

            //insert new skip_array
            if(count($new_skip) > 0)
            {
                $sql_insert_new_skip = "insert into temp_aed_skip_info (schedule_id, lesson_id, schedule_date, start_time_index, end_time_index, accname) values ";
                foreach($new_skip as $a_skip)
                {
                    $lesson_id = $a_skip['lesson_id'];
                    $start_skip = $a_skip['start_time'];
                    $end_skip = $start_skip + 1;
                    $sql_insert_new_skip .= "(-1, '$lesson_id', '$schedule_date', $start_skip, $end_skip, '$accname_new'),";
                }
                $sql_insert_new_skip = substr($sql_insert_new_skip, 0, -1).';';
                $insert_new = Constant::sql_execute($db_con, $sql_insert_new_skip);
                if(is_null($insert_new))
                {
                    throw new DBException('Fail to insert new relief', __FILE__, __LINE__, 2);
                }
            }
        }

        return true;
    }

    public static function approve($schedule_index, $date)
    {
        $teacher_contact = Teacher::getTeacherContact();

        $db_con = Constant::connect_to_db("ntu");
        if (empty($db_con))
        {
            throw new DBException('Fail to approve the schedule', __FILE__, __LINE__);
        }
        
        //0. mark leave
        $arrLeaveId = $_SESSION["leaveIds"];
        
        if(count($arrLeaveId) > 0)
        {
            $sql_mark_scheduled = "insert ignore into rs_leave_scheduled values ";
            foreach($arrLeaveId as $a_leave)
            {
                $sql_mark_scheduled .= "($a_leave, '$date'),";
            }
            $sql_mark_scheduled = substr($sql_mark_scheduled, 0, -1) . ';';
            $mark_schedule_result = Constant::sql_execute($db_con, $sql_mark_scheduled);
            if (is_null($mark_schedule_result))
            {
                throw new DBException("Fail to mark scheduled leave", __FILE__, __LINE__, 2);
            }
        }
        unset($_SESSION["leaveIds"]);
        
        //1. notify override old approved result
        //notify relief
        $sql_override_relief = "select relief_id from rs_relief_info where DATE(schedule_date) = DATE('" . $date . "');";
        $override_relief = Constant::sql_execute($db_con, $sql_override_relief);
        if(is_null($override_relief))
        {
            throw new DBException('Fail to clear exist relief record', __FILE__, __LINE__, 2);
        }
        $relief_delete_list = array();
        foreach($override_relief as $row)
        {
            $relief_delete_list[] = $row["relief_id"];
        }
        
        //notify skip
        $sql_override_skip = "select skip_id from rs_aed_skip_info where DATE(schedule_date) = DATE('" . $date . "');";
        $override_skip = Constant::sql_execute($db_con, $sql_override_skip);
        if(is_null($override_skip))
        {
            throw new DBException('Fail to clear exist relief record', __FILE__, __LINE__, 2);
        }
        $skip_delete_list = array();
        foreach($override_skip as $row)
        {
            $skip_delete_list[] = $row["skip_id"];
        }
        
        //2. notify
        Notification::sendReliefNotification($schedule_index, $relief_delete_list, $skip_delete_list, $teacher_contact, $date);
        
        //3. clear old result
        $sql_clear_relief = "delete from rs_relief_info where DATE(schedule_date) = DATE('" . $date . "');";
        $clear_result = Constant::sql_execute($db_con, $sql_clear_relief);
        if (is_null($clear_result))
        {
            throw new DBException('Fail to clear exist relief record', __FILE__, __LINE__, 2);
        }
        
        $sql_clear_skip = "delete from rs_aed_skip_info where DATE(schedule_date) = DATE('" . $date . "');";
        $clear_skip = Constant::sql_execute($db_con, $sql_clear_skip);
        if (is_null($clear_skip))
        {
            throw new DBException('Fail to clear exist relief record', __FILE__, __LINE__, 2);
        }
        
        //4. insert new relief
        //get leaves
        $sql_select_leave = "select * from rs_leave_info where DATE('$date') between DATE(start_time) and DATE(end_time);";
        $select_leave = Constant::sql_execute($db_con, $sql_select_leave);
        if(is_null($select_leave))
        {
            throw new DBException('Fail to clear exist relief record', __FILE__, __LINE__, 2);
        }

        $leave_dict = array();
        foreach($select_leave as $row)
        {
            $accname = $row['teacher_id'];

            if(empty($leave_dict[$accname]))
            {
                $leave_dict[$accname] = array();
            }

            $leave_id = $row['leave_id'];

            $time_zone = new DateTimeZone('Asia/Singapore');

            $start_time_str = $row['start_time'];
            $start_time_obj = new DateTime($start_time_str);
            $start_time_obj->setTimezone($time_zone);
            $start_time_stamp = $start_time_obj->getTimestamp();

            $end_time_str = $row['end_time'];
            $end_time_obj = new DateTime($end_time_str);
            $end_time_obj->setTimezone($time_zone);
            $end_time_stamp = $end_time_obj->getTimestamp();

            $leave_dict[$accname][] = array($start_time_stamp, $end_time_stamp, $leave_id);
        }

        //copy approved schedule from temp table and find leave_id_ref for each relief
        $sql_select_temp = "select lesson_id, schedule_date, start_time_index, end_time_index, leave_teacher, relief_teacher, num_of_slot, leave_id_ref from temp_each_alternative where schedule_id = $schedule_index";
        $select_temp = Constant::sql_execute($db_con, $sql_select_temp);
        if (is_null($select_temp))
        {
            throw new DBException('Fail to approve the schedule', __FILE__, __LINE__, 2);
        }

        if(count($select_temp) > 0)
        {
            $sql_insert_select = "insert into rs_relief_info (lesson_id, schedule_date, start_time_index, end_time_index, leave_teacher, relief_teacher, num_of_slot, leave_id_ref) values ";
            foreach($select_temp as $row)
            {
                $lesson_id = $row['lesson_id'];
                $schedule_date = $row['schedule_date'];
                $start_time_relief = $row['start_time_index'];
                $end_time_relief = $row['end_time_index'];
                $leave_teacher = $row['leave_teacher'];
                $relief_teacher = $row['relief_teacher'];
                $num_of_slot = $row['num_of_slot'];

                $start_time_value = SchoolTime::getTimeValue($start_time_relief);

                $start_time_relief_obj = new DateTime($schedule_date." ".$start_time_value);
                $start_time_relief_obj->setTimezone($time_zone);
                $start_time_relief_stamp = $start_time_relief_obj->getTimestamp();

                $leave_id_ref = "NULL";
                foreach($leave_dict[$leave_teacher] as $row)
                {
                    if($start_time_relief_stamp >= $row[0] && $start_time_relief_stamp <= $row[1])
                    {
                        $leave_id_ref = $row[2];
                    }
                }

                $sql_insert_select .= "('$lesson_id', '$schedule_date', $start_time_relief, $end_time_relief, '$leave_teacher', '$relief_teacher', $num_of_slot, $leave_id_ref),";
            }
            $sql_insert_select = substr($sql_insert_select, 0, -1).';';

            $insert_result = Constant::sql_execute($db_con, $sql_insert_select);
            if (is_null($insert_result))
            {
                throw new DBException('Fail to approve the schedule', __FILE__, __LINE__, 2);
            }
        }

        //5. insert new skip
        //get list of relief to construct skip reference
        $sql_select_relief = "select * from rs_relief_info where schedule_date = DATE('$date')";
        $select_relief_result = Constant::sql_execute($db_con, $sql_select_relief);
        if(is_null($select_relief_result))
        {
            throw new DBException('Fail to clear temporary schedules', __FILE__, __LINE__, 2);
        }
        $relief_dict = array();
        foreach($select_relief_result as $row)
        {
            $accname = $row['relief_teacher'];
            if(empty($relief_dict[$accname]))
            {
                $relief_dict[$accname] = array();
            }

            $start_time = $row['start_time_index'];
            $end_time = $row['end_time_index'];
            $relief_id = $row['relief_id'];

            for($i = $start_time; $i < $end_time; $i++)
            {
                $relief_dict[$accname][$i] = $relief_id;
            }
        }

        //copy selected one
        $sql_select_temp_skip = "select * from temp_aed_skip_info where schedule_id = $schedule_index;";
        $select_temp_skip = Constant::sql_execute($db_con, $sql_select_temp_skip);
        if(is_null($select_temp_skip))
        {
            throw new DBException('Fail to clear exist skip record', __FILE__, __LINE__, 2);
        }

        if(count($select_temp_skip) > 0)
        {
            $sql_insert_skip = "insert into rs_aed_skip_info (lesson_id, schedule_date, start_time_index, end_time_index, accname, relief_id_ref) values ";
            foreach($select_temp_skip as $row)
            {
                $lesson_id = $row['lesson_id'];
                $start_time_skip = $row['start_time_index'];
                $end_time_skip = $start_time_skip + 1;
                $accname = $row['accname'];
                $relief_id_ref = empty($relief_dict[$accname][$start_time_skip])?'NULL':$relief_dict[$accname][$start_time_skip];

                $sql_insert_skip .= "('$lesson_id', '$date', $start_time_skip, $end_time_skip, '$accname', $relief_id_ref),";
            }
            $sql_insert_skip = substr($sql_insert_skip, 0, -1).';';

            $insert_skip_result = Constant::sql_execute($db_con, $sql_insert_skip);
            if (is_null($insert_skip_result))
            {
                throw new DBException('Fail to approve the schedule', __FILE__, __LINE__, 2);
            }
        }

        //6. clear temp tables - relief and skip
        $sql_delete = "delete from temp_each_alternative;";
        $delete_result = Constant::sql_execute($db_con, $sql_delete);
        if (is_null($delete_result))
        {
            throw new DBException('Fail to clear temporary schedules', __FILE__, __LINE__, 2);
        }
        
        $sql_delete_skip = "delete from temp_aed_skip_info;";
        $delete_skip_result = Constant::sql_execute($db_con, $sql_delete_skip);
        if (is_null($delete_skip_result))
        {
            throw new DBException('Fail to clear temporary skip record', __FILE__, __LINE__, 2);
        }
    }

    public static function allSchduleIndex()
    {
        $db_con = Constant::connect_to_db("ntu");
        if (empty($db_con))
        {
            throw new DBException('Fail to query schedule index', __FILE__, __LINE__);
        }

        $sql_index = "select distinct schedule_id from temp_each_alternative;";
        $index_result = Constant::sql_execute($db_con, $sql_index);
        if (is_null($index_result))
        {
            throw new DBException('Fail to query schedule index', __FILE__, __LINE__, 2);
        }

        $result = Array();
        foreach ($index_result as $row)
        {
             $temp = $row['schedule_id'] - 0;
             
             if($temp === -1)
             {
                 continue;
             }
             
             $result[] = $temp;
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
