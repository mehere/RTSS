<?php

require_once 'util.php';
require_once 'Teacher.php';

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Scheduling
{
    private $date;
    private $leave_dict;
    private $relief_dict;
    private $on_leave_info;
    private $teacher_list;
    
    public function __construct($date)
    {
        $this->date = $date;
        $this->leave_dict = Array();
        $this->relief_dict = Array();
        $this->on_leave_info = Teacher::getTeacherOnLeave($date);
        
        $this->teacher_list = Teacher::getAllTeachers();
        
        $db_url = Constant::db_url;
        $db_username = Constant::db_username;
        $db_password = Constant::db_password;
        $db_name = Constant::db_name;
        
        $db_con = mysql_connect($db_url, $db_username, $db_password);
        
        if (!$db_con)
        {
           return;
        }
        
        mysql_select_db($db_name);
        
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
    }
    
    public function getNormalLessonsToday()
    {
        $result = Array(
            "success" => false,
            "error_msg" => NULL,
            "teachers" => Array()
        );
        
        $lesson_dict = Array();
        $teacher_dict = Array();
        $all_normal_teachers = Teacher::getTeacherName("normal");
        //convert date to weekday
        $weekday_string = date("D",  strtotime($this->date));
        
        $weekday_number = 0;
        
        switch ($weekday_string)
        {
            case "Mon":$weekday_number=1;break;
            case "Tue":$weekday_number=2;break;
            case "Wed":$weekday_number=3;break;
            case "Thu":$weekday_number=4;break;
            case "Fri":$weekday_number=5;break;
            case "Sat":
            case "Sun":
            default:{
                $result["error_msg"] = "Possible errors : 1. The input date is not a weekday. The weekday should be between Monday and Friday. 2. There are errors in date format";
                return $result;
            }
        }
        
        //db connection
        $db_url = Constant::db_url;
        $db_username = Constant::db_username;
        $db_password = Constant::db_password;
        $db_name = Constant::db_name;
        
        $db_con = mysql_connect($db_url, $db_username, $db_password);
        
        if (!$db_con)
        {
            return $result;
        }
        
        mysql_select_db($db_name);
        
        //create teacher dict
        foreach($all_normal_teachers as $a_normal)
        {
            $temp_normal = new Teacher("dummy name");
            $temp_normal->accname = $a_normal["accname"];
            $temp_normal->name = $a_normal["fullname"];;

            if(array_key_exists($a_normal["accname"], $this->leave_dict))
            {
                $temp_normal->noLessonMissed = $this->leave_dict[$a_normal["accname"]];
            }
            if(array_key_exists($a_normal["accname"], $this->relief_dict))
            {
                $temp_normal->noLessonRelived = $this->relief_dict[$a_normal["accname"]];
            }

            $teacher_dict[$a_normal["accname"]] = $temp_normal;
        }
        
        //create lesson dictionary
        $sql_query_lessons = "select * from ct_lesson where weekday = ".$weekday_number." and type = 'N';";
        $lesson_query_result = mysql_query($sql_query_lessons);
        
        if(!$lesson_query_result)
        {
            $result["error_msg"] = "Database query error. Please contact database admin";
            return $result;
        }
        
        while($row =  mysql_fetch_array($lesson_query_result))
        {
             $lesson_id = $row["lesson_id"];
             $start_time = $row["start_time"];
             
             $date_object = new DayTime($weekday_number, $start_time);
             $one_lesson = new Lesson($date_object, $row["subj_code"], $row["venue"]);

             $one_lesson->endTimeSlot = $row["end_time"];
             
             $lesson_dict[$lesson_id] = $one_lesson;
        }
        
        //class
        $sql_query_class = "SELECT ct_class_matching.* FROM ct_class_matching, ct_lesson WHERE ct_lesson.lesson_id = ct_class_matching.lesson_id 
            AND ct_lesson.weekday = ".$weekday_number." AND ct_lesson.type = 'N';";
        $class_query_result = mysql_query($sql_query_class);
        
        if(!$class_query_result)
        {
            $result["error_msg"] = "Database query error. Please contact database admin";
            return $result;  
        }
        
        while($row =  mysql_fetch_array($class_query_result))
        {
            $one_class = new Students($row['class_name']);
            $the_lesson =$lesson_dict[$row['lesson_id']];
            array_push($the_lesson->classes, $one_class); 
        }
        
        //teacher with their classes
        $sql_query_teacher = "SELECT ct_teacher_matching.*, ct_name_abbre_matching.abbre_name FROM ct_teacher_matching, ct_lesson, ct_name_abbre_matching WHERE ct_lesson.lesson_id = ct_teacher_matching.lesson_id 
            AND ct_lesson.weekday = ".$weekday_number." AND ct_teacher_matching.teacher_id = ct_name_abbre_matching.teacher_id AND ct_lesson.type = 'N';";
        $teacher_query_result = mysql_query($sql_query_teacher);
        
        if(!$teacher_query_result)
        {
            $result["error_msg"] = "Database query error. Please contact database admin";
            return $result;
        }
        
        while($row =  mysql_fetch_array($teacher_query_result))
        {
            $acc_name = $row['teacher_id'];
            
            if(array_key_exists($acc_name, $teacher_dict))
            {
                $one_teacher = $teacher_dict[$acc_name];
            }
            else
            {
                $abbreviation = $row['abbre_name'];
                $one_teacher = new Teacher($abbreviation);
                $one_teacher->accname = $acc_name;
                $one_teacher->name = $this->teacher_list[$acc_name]["name"];
                
                if(array_key_exists($acc_name, $this->leave_dict))
                {
                    $one_teacher->noLessonMissed = $this->leave_dict[$acc_name];
                }
                if(array_key_exists($acc_name, $this->relief_dict))
                {
                    $one_teacher->noLessonRelived = $this->relief_dict[$acc_name];
                }
                
                $teacher_dict[$acc_name] = $one_teacher;
            }
            
            $the_lesson = $lesson_dict[$row['lesson_id']];
            
            for($i=$the_lesson->startTimeSlot;$i<$the_lesson->endTimeSlot;$i++)
            {
                if(!array_key_exists($i, $one_teacher->timetable))
                {
                    $one_teacher->timetable[$i] = $the_lesson;
                }
            }
            
            array_push($the_lesson->teachers, $one_teacher); 
        }
        
        //query leave
        foreach($this->on_leave_info as $a_info)
        {
            if($a_info['type'] !== 'Teacher')
            {
                continue;
            }
            
            if(array_key_exists($a_info['accname'], $teacher_dict))
            {
                $teacher_dict[$a_info['accname']]->leave[] = Scheduling::trimTimePeriod($a_info['datetime'][0][0], $a_info['datetime'][1][0], $a_info['datetime'][0][1], $a_info['datetime'][1][1], $this->date, $a_info['leaveID']);
            }
            else
            {
                $new_teacher = new Teacher("dummy_name");
                $new_teacher->name=$a_info['fullname'];
                
                if(array_key_exists($a_info['accname'], $this->leave_dict))
                {
                    $new_teacher->noLessonMissed = $this->leave_dict[$a_info['accname']];
                }
                if(array_key_exists($a_info['accname'], $this->relief_dict))
                {
                    $new_teacher->noLessonRelived = $this->relief_dict[$a_info['accname']];
                }
                
                $new_teacher->leave[] = Scheduling::trimTimePeriod($a_info['datetime'][0][0], $a_info['datetime'][1][0], $a_info['datetime'][0][1], $a_info['datetime'][1][1], $this->date, $a_info['leaveID']);
                
                $teacher_dict[$a_info['accname']] = $new_teacher;
            }
        }
        
        $result['success'] = true;
        $result['teachers'] = $teacher_dict;
        
        return $result;
    }
    
    public function getAEDLessonsToday()
    {
        $result = Array(
            "success" => false,
            "error_msg" => NULL,
            "teachers" => Array()
        );
        
        $lesson_dict = Array();
        $teacher_dict = Array();
        $highlight_dict = Array();
        $all_aed_teachers = Teacher::getTeacherName("AED");
        
        //convert date to weekday
        $weekday_string = date("D",  strtotime($this->date));
        
        $weekday_number = 0;
        
        switch ($weekday_string)
        {
            case "Mon":$weekday_number=1;break;
            case "Tue":$weekday_number=2;break;
            case "Wed":$weekday_number=3;break;
            case "Thu":$weekday_number=4;break;
            case "Fri":$weekday_number=5;break;
            case "Sat":
            case "Sun":
            default:{
                $result["error_msg"] = "Possible errors : 1. The input date is not a weekday. The weekday should be between Monday and Friday. 2. There are errors in date format";
                return $result;
            }
        }
        
        //db connection
        $db_url = Constant::db_url;
        $db_username = Constant::db_username;
        $db_password = Constant::db_password;
        $db_name = Constant::db_name;
        
        $db_con = mysql_connect($db_url, $db_username, $db_password);
        
        if (!$db_con)
        {
            $result["error_msg"] = "DB connection error 1";
            return $result;
        }
        
        mysql_select_db($db_name);
        
        //highlighted lessons
        $sql_query_highlighted = "select * from ct_aed_highlight;";
        $query_highlighted_result = mysql_query($sql_query_highlighted);
        if(!$query_highlighted_result)
        {
            $result["error_msg"] = "query highlight lesson error";
            return $result;
        }
        while($row = mysql_fetch_array($query_highlighted_result))
        {
            $highlight_dict[] = $row['lesson_id'];
        }
        
        //create teacher dict
        foreach($all_aed_teachers as $a_aed)
        {
            $temp_aed = new Teacher("dummy name");
            $temp_aed->accname = $a_aed["accname"];
            $temp_aed->name = $a_aed["fullname"];;

            if(array_key_exists($a_aed["accname"], $this->leave_dict))
            {
                $temp_aed->noLessonMissed = $this->leave_dict[$a_aed["accname"]];
            }
            if(array_key_exists($a_aed["accname"], $this->relief_dict))
            {
                $temp_aed->noLessonRelived = $this->relief_dict[$a_aed["accname"]];
            }

            $teacher_dict[$a_aed["accname"]] = $temp_aed;
        }
        
        //create lesson dictionary
        $sql_query_lessons = "select * from ct_lesson where weekday = ".$weekday_number." and type = 'A';";
        $lesson_query_result = mysql_query($sql_query_lessons);
        
        if(!$lesson_query_result)
        {
            $result["error_msg"] = "Database query error. Please contact database admin";
            return $result;
        }
        
        while($row =  mysql_fetch_array($lesson_query_result))
        {
             $lesson_id = $row["lesson_id"];
             $start_time = $row["start_time"];
             
             $date_object = new DayTime($weekday_number, $start_time);
             $one_lesson = new Lesson($date_object, $row["subj_code"], $row["venue"]);

             $one_lesson->endTimeSlot = $row["end_time"];
             
             if(!in_array($lesson_id, $highlight_dict))
             {
                 $one_lesson->isHighlighted = false;
             }
             
             $lesson_dict[$lesson_id] = $one_lesson;
        }
        
        //class
        $sql_query_class = "SELECT ct_class_matching.* FROM ct_class_matching, ct_lesson WHERE ct_lesson.lesson_id = ct_class_matching.lesson_id 
            AND ct_lesson.weekday = ".$weekday_number." AND ct_lesson.type = 'A';";
        $class_query_result = mysql_query($sql_query_class);
        
        if(!$class_query_result)
        {
            $result["error_msg"] = "Database query error. Please contact database admin";
            return $result;  
        }
        
        while($row =  mysql_fetch_array($class_query_result))
        {
            $one_class = new Students($row['class_name']);
            $the_lesson =$lesson_dict[$row['lesson_id']];
            array_push($the_lesson->classes, $one_class); 
        }
        
        //teacher with their classes
        $sql_query_teacher = "SELECT ct_teacher_matching.*, ct_name_abbre_matching.abbre_name FROM ct_teacher_matching, ct_lesson, ct_name_abbre_matching WHERE ct_lesson.lesson_id = ct_teacher_matching.lesson_id 
            AND ct_lesson.weekday = ".$weekday_number." AND ct_teacher_matching.teacher_id = ct_name_abbre_matching.teacher_id AND ct_lesson.type = 'A';";
        $teacher_query_result = mysql_query($sql_query_teacher);
        
        if(!$teacher_query_result)
        {
            $result["error_msg"] = "Database query error. Please contact database admin";
            return $result;
        }
        
        while($row =  mysql_fetch_array($teacher_query_result))
        {
            $acc_name = $row['teacher_id'];
            
            if(array_key_exists($acc_name, $teacher_dict))
            {
                $one_teacher = $teacher_dict[$acc_name];
            }
            else
            {
                $abbreviation = $row['abbre_name'];
                $one_teacher = new Teacher($abbreviation);
                $one_teacher->accname = $acc_name;
                $one_teacher->name = $this->teacher_list[$acc_name]["name"];
                
                if(array_key_exists($acc_name, $this->leave_dict))
                {
                    $one_teacher->noLessonMissed = $this->leave_dict[$acc_name];
                }
                if(array_key_exists($acc_name, $this->relief_dict))
                {
                    $one_teacher->noLessonRelived = $this->relief_dict[$acc_name];
                }
                
                $teacher_dict[$acc_name] = $one_teacher;
            }
            
            $the_lesson = $lesson_dict[$row['lesson_id']];
            
            for($i=$the_lesson->startTimeSlot;$i<$the_lesson->endTimeSlot;$i++)
            {
                if(!array_key_exists($i, $one_teacher->timetable))
                {
                    $one_teacher->timetable[$i] = $the_lesson;
                }
            }
            
            array_push($the_lesson->teachers, $one_teacher); 
        }
        
        //query leave
        foreach($this->on_leave_info as $a_info)
        {
            if($a_info['type'] !== 'AED')
            {
                continue;
            }
            
            if(array_key_exists($a_info['accname'], $teacher_dict))
            {
                $teacher_dict[$a_info['accname']]->leave[] = Scheduling::trimTimePeriod($a_info['datetime'][0][0], $a_info['datetime'][1][0], $a_info['datetime'][0][1], $a_info['datetime'][1][1], $this->date, $a_info['leaveID']);
            }
            else
            {
                $new_teacher = new Teacher("dummy_name");
                $new_teacher->name=$a_info['fullname'];
                
                if(array_key_exists($a_info['accname'], $this->leave_dict))
                {
                    $new_teacher->noLessonMissed = $this->leave_dict[$a_info['accname']];
                }
                if(array_key_exists($a_info['accname'], $this->relief_dict))
                {
                    $new_teacher->noLessonRelived = $this->relief_dict[$a_info['accname']];
                }
                
                $new_teacher->leave[] = Scheduling::trimTimePeriod($a_info['datetime'][0][0], $a_info['datetime'][1][0], $a_info['datetime'][0][1], $a_info['datetime'][1][1], $this->date, $a_info['leaveID']);
                
                $teacher_dict[$a_info['accname']] = $new_teacher;
            }
        }
        
        $result['success'] = true;
        $result['teachers'] = $teacher_dict;
        
        return $result;
    }
    
    public function getUntrainedTeachers()
    {
        $result = Array(
            "success" => false,
            "error_msg" => NULL,
            "teachers" => Array()
        );
        
        $all_untrained = Teacher::getTeacherName("untrained");
        $leave_untrained = Array();
        
        foreach($this->on_leave_info as $a_info)
        {
            if(strcmp($a_info['type'], 'untrained') === 0)
            {
                $leave_untrained[] = $a_info['accname'];
            }
        }
        
        foreach($all_untrained as $an_untrained)
        {
            if(in_array($an_untrained['accname'], $leave_untrained))
            {
                continue;
            }
            
            $a_teacher = new Teacher("dummy name");
            $a_teacher->accname = $an_untrained['accname'];
            $a_teacher->name = $an_untrained['fullname'];
            
            if(array_key_exists($a_teacher->accname, $this->leave_dict))
            {
                $a_teacher->noLessonMissed = $this->leave_dict[$a_teacher->accname];
            }
            if(array_key_exists($a_teacher->accname, $this->relief_dict))
            {
                $a_teacher->noLessonRelived = $this->relief_dict[$a_teacher->accname];
            }
            
            $result['teachers'][$a_teacher->accname] = $a_teacher;
        }
        
        $result["success"] = true;
        
        return $result;
    }
    
    public function getTempTeachers()
    {
        $result = Array(
            "success" => false,
            "error_msg" => NULL,
            "teachers" => Array()
        );
        
        $temp_teachers = Teacher::getTempTeacher($this->date);
        
        foreach($temp_teachers as $a_teacher)
        {
            if(array_key_exists($a_teacher['accname'], $result["teachers"]))
            {
                $the_teacher = $result["teachers"][$a_teacher['accname']];
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

                $result["teachers"][$the_teacher->accname] = $the_teacher;
            }
            
            $the_teacher->availability[] = Scheduling::trimTimePeriod($a_teacher['datetime'][0][0], $a_teacher['datetime'][1][0], $a_teacher['datetime'][0][1], $a_teacher['datetime'][1][1], $this->date, $a_teacher['availability_id']);
        }
        
        $result["success"] = true;
        return $result;
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
}
?>
