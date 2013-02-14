<?php

require_once 'util.php';
require_once 'Teacher.php';
require_once 'DBException.php';

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

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

        $db_con = Constant::connect_to_db("ntu");
        if (!$db_con)
        {
            throw new DBException("Fail to connect to database", __FILE__, __LINE__);
        }

        //query num_of_leave slot
        $sql_query_num_of_leave = "select teacher_id, sum(num_of_slot) as num_of_leave from rs_leave_info group by teacher_id";
        $query_num_of_leave_result = mysql_query($sql_query_num_of_leave);
        if(!$query_num_of_leave_result)
        {
            return;
        }
        while($row = mysql_fetch_assoc($query_num_of_leave_result))
        {
            $this->leave_dict[$row['teacher_id']] = $row['num_of_leave'];
        }

        //query num_of_relief slot
        $sql_query_num_of_relief = "select relief_teacher, sum(num_of_slot) as num_of_relief from rs_relief_info group by relief_teacher";
        $query_num_of_relief_result = mysql_query($sql_query_num_of_relief);
        if(!$query_num_of_relief_result)
        {
            return;
        }
        while($row = mysql_fetch_assoc($query_num_of_relief_result))
        {
            $this->relief_dict[$row['teacher_id']] = $row['num_of_relief'];
        }

        //create lesson dictionary
        $this->lesson_list = Array();
        
        $sql_query_lessons = "select * from ct_lesson where weekday = ".$this->weekday.";";
        $lesson_query_result = mysql_query($sql_query_lessons);

        if(!$lesson_query_result)
        {
            throw new DBException("Fail to query lesson from database", __FILE__, __LINE__);
        }

        while($row =  mysql_fetch_array($lesson_query_result))
        {
             $lesson_id = $row["lesson_id"];
             $start_time = $row["start_time"];

             $date_object = new DayTime($this->weekday, $start_time);
             $one_lesson = new Lesson($date_object, $row["subj_code"], $row["venue"]);

             $one_lesson->endTimeSlot = $row["end_time"];
             $one_lesson->lessonId = $row["lesson_id"];
             
             if(strcmp($row['type'], 'A')===0)
             {
                 $one_lesson->isHighlighted = $row['highlighted'];
             }
             
             $this->lesson_list[$lesson_id] = $one_lesson;
        }
        
        //class of one lesson
        $sql_query_class = "SELECT ct_class_matching.* FROM ct_class_matching, ct_lesson WHERE ct_lesson.lesson_id = ct_class_matching.lesson_id 
            AND ct_lesson.weekday = ".$this->weekday.";";
        $class_query_result = mysql_query($sql_query_class);

        if(!$class_query_result)
        {
            throw new DBException("Fail to query class from database", __FILE__, __LINE__);
        }

        while($row =  mysql_fetch_array($class_query_result))
        {
            $one_class = new Students($row['class_name']);
            $the_lesson =$this->lesson_list[$row['lesson_id']];
            $the_lesson->classes[] = $one_class; 
        }
        
        //teacher-lesson match list
        $this->teacher_lesson_list = Array();
        
        $sql_query_teacher = "SELECT ct_teacher_matching.* FROM ct_teacher_matching, ct_lesson WHERE ct_lesson.lesson_id = ct_teacher_matching.lesson_id AND ct_lesson.weekday = ".$this->weekday.";";
        $teacher_query_result = mysql_query($sql_query_teacher);
        if(!$teacher_query_result)
        {
            throw new DBException("Fail to query teacher from database", __FILE__, __LINE__);
        }
        
        while($row =  mysql_fetch_array($teacher_query_result))
        {
            if(!array_key_exists($row['teacher_id'], $this->teacher_lesson_list))
            {
                $this->teacher_lesson_list[$row['teacher_id']] = Array();
            }
            
            $this->teacher_lesson_list[$row['teacher_id']][] = $row['lesson_id'];
        }
        
        //teacher-class match list
        $this->teacher_class_list = Array();
        
        $sql_query_teacher_class = "Select ct_teacher_matching.teacher_id as teacher, ct_class_matching.class_name as class from ct_lesson, ct_teacher_matching, ct_class_matching where ct_lesson.lesson_id = ct_teacher_matching.lesson_id and ct_lesson.lesson_id = ct_class_matching.lesson_id;";
        $query_teacher_class_result = mysql_query($sql_query_teacher_class);
        if(!$query_teacher_class_result)
        {
            throw new DBException("Fail to query teacher-class from database", __FILE__, __LINE__);
        }
        
        while($row =  mysql_fetch_array($query_teacher_class_result))
        {
            if(!array_key_exists($row['teacher'], $this->teacher_class_list))
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
        foreach($this->on_leave_info as $a_info)
        {
            $leave_time = SchedulerDB::trimTimePeriod($a_info['datetime'][0][0], $a_info['datetime'][1][0], $a_info['datetime'][0][1], $a_info['datetime'][1][1], $this->date_str, $a_info['leaveID']);

            $a_leave = Array(
                "startLeave" => $leave_time[0],
                "endLeave" => $leave_time[1]
            );

            $algo_type = Constant::$teacher_type[$a_info['type']];
            if(array_key_exists($a_info['accname'], $result[$algo_type]))
            {
                $result[$algo_type][$a_info['accname']][] = $a_leave;
            }
            else
            {
                $result[$algo_type][$a_info['accname']] = Array($a_leave);
            }
        }

        return $result;
    }

    public function getExcludedTeachers()
    {
        $db_con = Constant::connect_to_db("ntu");
        if(empty($db_con))
        {
            throw new DBException("Fail to connect to database", __FILE__, __LINE__);
        }
        $sql_query_exclude = "select * from rs_exclude_list;";
        $query_exclude_result = mysql_query($sql_query_exclude);
        if(!$query_exclude_result)
        {
            throw new DBException("Fail to query exclude list from database", __FILE__, __LINE__);
        }

        $result = Array(
            "Temp" => Array(),
            "Aed" => Array(),
            "Untrained" => Array(),
            "Normal" => Array(),
            "Hod" => Array()
        );

        $db_type = array_keys(Constant::$teacher_type);

        while($row = mysql_fetch_assoc($query_exclude_result))
        {
            $teacher_id = $row['teacher_id'];

            if(!empty($this->teacher_list[$teacher_id]))
            {
                $type = $this->teacher_list[$teacher_id]['type'];
            }
            else if(!empty($this->temp_list[$teacher_id]))
            {
                $type = $db_type[2];
            }
            else
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
        
        $db_con = Constant::connect_to_db("ntu");
        if(empty($db_con))
        {
            throw new DBException("Fail to connect to database", __FILE__, __LINE__); 
        }
        
        $sql_query_speciality = "select * from ct_aed_speciality;";
        $query_speciality_result = mysql_query($sql_query_speciality);
        if(!$query_speciality_result)
        {
            throw new DBException("Fail to query aed speciality", __FILE__, __LINE__);
        }
        
        while($row = mysql_fetch_assoc($query_speciality_result))
        {
            if(array_key_exists($row['teacher_id'], $result))
            {
                $result[$row['teacher_id']]->speciality = $row['speciality'];
            }
        }
        
        return $result;
    }

    public function getTempTeachers()
    {
        $result_list = Array();

        foreach($this->temp_list as $a_teacher)
        {
            if(array_key_exists($a_teacher['accname'], $result_list))
            {
                $the_teacher = $result_list[$a_teacher['accname']];
            }
            else
            {
                $the_teacher = new Teacher('dummy name');

                $the_teacher->accname = $a_teacher['accname'];
                $the_teacher->name = $a_teacher['fullname'];

                if(array_key_exists($the_teacher->accname, $this->leave_dict))
                {
                    $the_teacher->noLessonMissed = $this->leave_dict[$the_teacher->accname];
                }
                if(array_key_exists($the_teacher->accname, $this->relief_dict))
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

        if($start_diff->d!==0 && $end_diff->d!==0)
        {
            return array(1,15, $leave_id);
        }
        else if($start_diff->d===0 && $end_diff->d!==0)
        {
            return array(Constant::$inverse_time_conversion[str_replace(":", "", $start_time)], 15, $leave_id);
        }
        else if($start_diff->d!==0 && $end_diff->d===0)
        {
            return array(1, Constant::$inverse_time_conversion[str_replace(":", "", $end_time)], $leave_id);
        }
        else
        {
            return array(Constant::$inverse_time_conversion[str_replace(":", "", $start_time)], Constant::$inverse_time_conversion[str_replace(":", "", $end_time)], $leave_id);
        }
    }

    private function customizeTeacherList($type)
    {
        $teacher_dict = Array();
        $result = Teacher::getTeacherName($type);

        foreach($result as $a_normal)
        {
            $temp_normal = new Teacher("dummy name");
            //accname
            $temp_normal->accname = $a_normal["accname"];
            //name
            $temp_normal->name = $a_normal["fullname"];;

            //noLessonMissed
            if(array_key_exists($a_normal["accname"], $this->leave_dict))
            {
                $temp_normal->noLessonMissed = $this->leave_dict[$a_normal["accname"]];
            }
            //noLessonRelived
            if(array_key_exists($a_normal["accname"], $this->relief_dict))
            {
                $temp_normal->noLessonRelived = $this->relief_dict[$a_normal["accname"]];
            }

            //timetable
            if(array_key_exists($a_normal['accname'], $this->teacher_lesson_list))
            {
                foreach($this->teacher_lesson_list[$a_normal["accname"]] as $lesson_id)
                {
                    $the_lesson = $this->lesson_list[$lesson_id];

                    for($i=$the_lesson->startTimeSlot;$i<$the_lesson->endTimeSlot;$i++)
                    {
                        if(!array_key_exists($i, $temp_normal->timetable))
                        {
                            $temp_normal->timetable[$i] = $the_lesson;
                        }
                    }
                }
            }
            
            //classes of teacher
            if(array_key_exists($a_normal["accname"], $this->teacher_class_list))
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
}
?>
