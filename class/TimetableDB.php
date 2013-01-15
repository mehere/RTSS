<?php
require_once 'util.php';

class TimetableDB
{
    //this function insert lesson_list into database
    //input : lesson_list
    //output : error message string, echo to read it. empty if no error. Fatal error message starts with FE.
    public static function insertTimetable($lesson_list)
    {
        //error information
        $empty_days = Array();
        $empty_start_time = Array();
        $empty_end_time = Array();
        $empty_subject = Array();
        $empty_class = Array();
        $empty_teacher = Array();
        
        //sql statement construction
        $sql_insert_lesson = "insert into ct_lesson values ";
        $sql_insert_lesson_class = "insert into ct_class_matching values ";
        $sql_insert_lesson_teacher = "insert into ct_teacher_matching values ";
        
        $lesson_id=0;   
        //a unique identifier for lesson
        //why i dont use $key: what if the $key is string not int
        //why i dont use auto increment : need to insert one record in one loop, not as efficient as aggregate insert
        foreach($lesson_list as $key=>$value){
            $lesson_id++;
            
            //insert into ct_lesson table
            $subject = $value->subject;
            $day_index = $value->day;
            $start_time_index = $value->startTimeSlot;
            $end_time_index = $value->endTimeSlot;
            $venue = "";
            if (!(empty($value->venue))){
                $venue = $value->venue;
            }
            
            if(empty($subject))
            {
                array_push($empty_subject, $key);
                $subject  = Constant::default_var_value;
            }
            if(empty($day_index) || !is_numeric($day_index))
            {
                array_push($empty_days, $key);
                $day_index = Constant::default_int_value;
            }
            if(empty($start_time_index) || !is_numeric($start_time_index))
            {
                array_push($empty_start_time, $key);
                $start_time_index  = Constant::default_int_value;
            }
            if(empty($end_time_index) || !is_numeric($end_time_index))
            {
                array_push($empty_end_time, $key);
                $end_time_index  = Constant::default_int_value;
            }
            
            $start_time = Constant::$time_conversion[$start_time_index];
            $end_time = Constant::$time_conversion[$end_time_index];
            
            $sql_insert_lesson .= "(".$lesson_id.", ".$day_index.", '".mysql_real_escape_string($start_time)."', '".mysql_real_escape_string($end_time)."', '".mysql_real_escape_string($subject)."', '".mysql_real_escape_string($venue)."'), ";
            
            //insert into ct_class_matching
            $classes = $value->classes;
            
            foreach ($classes as $aClass) {
                $class_name = $aClass->name;
                
                if(empty($class_name))
                {
                    if(!in_array($key, $empty_class))
                    {
                        array_push($empty_class, $key);
                    }
                    
                    $class_name  = Constant::default_var_value;
                }
                
                $sql_insert_lesson_class .= "(".$lesson_id.", '".mysql_real_escape_string($class_name)."'), ";
            }
            
            //insert into ct_teacher_matching
            $teachers = $value->teachers;
            
            $teachers_with_accname = Teacher::getTeachersAccnameAndFullname($teachers);
            
            foreach ($teachers_with_accname as $a_teacher){
                $teacher_accname = $a_teacher->accname;
                
                if(empty($teacher_accname))
                {
                    if(!in_array($a_teacher->abbreviation, $empty_teacher))
                    {
                        array_push($empty_teacher, $a_teacher->abbreviation);
                    }
                    
                    continue;
                }
                
                $sql_insert_lesson_teacher .= "('".mysql_real_escape_string($teacher_accname)."', ".$lesson_id."), ";
            }
        }
        
        $sql_insert_lesson = substr($sql_insert_lesson, 0, -2).';';
        $sql_insert_lesson_class = substr($sql_insert_lesson_class, 0, -2).';';
        $sql_insert_lesson_teacher = substr($sql_insert_lesson_teacher, 0, -2).';';
        
        //DB operation
        $db_url = Constant::db_url;
        $db_username = Constant::db_username;
        $db_password = Constant::db_password;
        $db_name = Constant::db_name;
        
        $db_con = mysql_connect($db_url, $db_username, $db_password);
        
        if (!$db_con)
        {
            return "Could not connect to database";
        }
        
        mysql_select_db($db_name, $db_con);
        
        //clear existing data
        $delete_sql_lesson = "delete from ct_lesson;";
        if (!mysql_query($delete_sql_lesson, $db_con))
        {
            return "Fail to clear database. Please contact system admin";
        }
        $delete_sql_class = "delete from ct_class_matching;";
        if (!mysql_query($delete_sql_class, $db_con))
        {
            return "Fail to clear database. Please contact system admin";
        }
        $delete_sql_teacher = "delete from ct_teacher_matching;";
        if (!mysql_query($delete_sql_teacher, $db_con))
        {
            return "Fail to clear database. Please contact system admin";
        }
        
        if(!mysql_query($sql_insert_lesson))
        {
            return "Error in ct_lesson table. Fail to insert timetable. Please try again later";
        }
        if(!mysql_query($sql_insert_lesson_class))
        {
            return "Error in ct_class_matching table. Fail to insert timetable. Please try again later";
        }
        if(!mysql_query($sql_insert_lesson_teacher))
        {
            return "Error in ct_teacher_matching table. Fail to insert timetable. Please try again later";
        }
        
        mysql_close($db_con);
        
        //print error info
        $err_message = "";
       
        if(count($empty_subject)>0)
        {
            $err_message .= "The following lessons have empty subject : <br><br>";
            foreach($empty_subject as $a_key)
            {
                $err_message .= " ".$a_key." ";
            }
            $err_message .= "<br><br>";
            $err_message .= "An invalid value is used as a replacement in database. This may cause unpredictable problems in future. Please check the timetable and timetable analyzer. Simply reload the timetable after problems are fixed.";
            $err_message .= "<br><br>";
        }
        if(count($empty_days)>0)
        {
            $err_message .= "The following lessons have empty day : <br><br>";
            foreach($empty_days as $a_key)
            {
                $err_message .= " ".$a_key." ";
            }
            $err_message .= "<br><br>";
            $err_message .= "An invalid value is used as a replacement in database. This may cause unpredictable problems in future. Please check the timetable and timetable analyzer. Simply reload the timetable after problems are fixed.";
            $err_message .= "<br><br>";
        }
        if(count($empty_start_time)>0)
        {
            $err_message .= "The following lessons have empty start time : <br><br>";
            foreach($empty_start_time as $a_key)
            {
                $err_message .= " ".$a_key." ";
            }
            $err_message .= "<br><br>";
            $err_message .= "An invalid value is used as a replacement in database. This may cause unpredictable problems in future. Please check the timetable and timetable analyzer. Simply reload the timetable after problems are fixed.";
            $err_message .= "<br><br>";
        }
        if(count($empty_end_time)>0)
        {
            $err_message .= "The following lessons have empty end time : <br><br>";
            foreach($empty_end_time as $a_key)
            {
                $err_message .= " ".$a_key." ";
            }
            $err_message .= "<br><br>";
            $err_message .= "An invalid value is used as a replacement in database. This may cause unpredictable problems in future. Please check the timetable and timetable analyzer. Simply reload the timetable after problems are fixed.";
            $err_message .= "<br><br>";
        }
        if(count($empty_class)>0)
        {
            $err_message .= "The following lessons have empty class : <br>";
            foreach($empty_class as $a_key)
            {
                $err_message .= " [ ".$a_key." ] ";
            }
            $err_message .= "<br>";
            $err_message .= "An invalid value is used as a replacement in database. This may cause unpredictable problems in future. Please check the timetable and timetable analyzer. Simply reload the timetable after problems are fixed.";
            $err_message .= "<br><br>";
        }
        if(count($empty_teacher)>0)
        {
            $err_message .= "The following abbreviation names do not exist in database : <br><br>";
            foreach($empty_teacher as $a_key)
            {
                $err_message .= " [ ".$a_key." ] ";
            }
            $err_message .= "<br><br>";
            $err_message .= "We could not store teaching information of these teachers in database. This may cause severe problems during scheduling. Please contact the system admin immediately. Simply reload the timetable after problems are fixed.";
            $err_message .= "<br><br>";
        }
        
        return $err_message;
    }
}
?>
