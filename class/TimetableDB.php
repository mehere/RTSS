<?php
require_once 'util.php';
require_once 'Teacher.php';

class TimetableDB
{
    
    /**
     * this function insert lesson_list into database
     * @param type $lesson_list
     * @param type $teacher_list
     * @param type $year  e.g. '12' representing 2012,  string
     * @param type $sem   '1' or '2'
     * @return Array : array of error message strings. Each of output[0] - output[6] represents a type of error. if, e.g. empty(output[1]), then there is no error type 1. echo output[0]~output[6] to see the error details. pay special attention of output[6], which represents abbre name not found error.
     */
    public static function insertTimetable($lesson_list, $teacher_list, $year='13', $sem='1')
    {
        //error information
        $error_array = Array(
            0 => NULL,
            1 => NULL,
            2 => NULL,
            3 => NULL,
            4 => NULL,
            5 => NULL,
            6 => NULL
        );
        
        $empty_days = Array();
        $empty_start_time = Array();
        $empty_end_time = Array();
        $empty_subject = Array();
        $empty_class = Array();
        $empty_teacher = Array();
     
        //teacher list
        Teacher::getTeachersAccnameAndFullname($teacher_list);
        
        //sql statement construction
        $sql_insert_lesson = "insert into ct_lesson (lesson_id, weekday, start_time, end_time, subj_code, venue, type) values ";
        $sql_insert_lesson_class = "insert into ct_class_matching values ";
        $sql_insert_lesson_teacher = "insert into ct_teacher_matching values ";
        
        //a unique identifier for lesson
        //why i dont use $key: what if the $key is string not int
        //why i dont use auto increment : need to insert one record in one loop, not as efficient as aggregate insert
        foreach($lesson_list as $key=>$value){
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
            if(empty($day_index) || !is_numeric($day_index) || $day_index < 1 || $day_index > Constant::num_of_week_day)
            {
                array_push($empty_days, $key);
                $day_index = Constant::default_num_value;
            }
            if(empty($start_time_index) || !is_numeric($start_time_index))
            {
                array_push($empty_start_time, $key);
                $start_time_index  = Constant::default_num_value;
            }
            if(empty($end_time_index) || !is_numeric($end_time_index))
            {
                array_push($empty_end_time, $key);
                $end_time_index  = Constant::default_num_value;
            }
            
            $lesson_id = TimetableDB::generateLessonPK('N', $year, $sem, $day_index, $start_time_index, $end_time_index, $value->classes, $value->teachers);
            
            $sql_insert_lesson .= "('".mysql_real_escape_string($lesson_id)."', ".$day_index.", ".$start_time_index.", ".$end_time_index.", '".mysql_real_escape_string($subject)."', '".mysql_real_escape_string($venue)."', 'N'), ";
            
            //insert into ct_class_matching
            $classes = $value->classes;
            
            if(count($classes)>0)
            {
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

                    $sql_insert_lesson_class .= "('".mysql_real_escape_string($lesson_id)."', '".mysql_real_escape_string($class_name)."'), ";
                }
            }
            
            //insert into ct_teacher_matching
            $teachers = $value->teachers;
            
            foreach ($teachers as $a_teacher){
                $abbre_name = $a_teacher->abbreviation;
                $teacher_accname = $teacher_list[$abbre_name]->accname;
                
                if(empty($teacher_accname))
                {
                    if(!in_array($abbre_name, $empty_teacher))
                    {
                        array_push($empty_teacher, $abbre_name);
                    }
                    
                    continue;
                }
                
                $sql_insert_lesson_teacher .= "('".mysql_real_escape_string($teacher_accname)."', '".mysql_real_escape_string($lesson_id)."'), ";
            }
        }
        
        $sql_insert_lesson = substr($sql_insert_lesson, 0, -2).';';
        $sql_insert_lesson_class = substr($sql_insert_lesson_class, 0, -2).';';
        $sql_insert_lesson_teacher = substr($sql_insert_lesson_teacher, 0, -2).';';
        
        /*
        echo $sql_insert_lesson.'<br><br>';
        echo $sql_insert_lesson_class.'<br><br>';
        echo $sql_insert_lesson_teacher.'<br><br>';
         * 
         */
        
        //DB operation
        $db_url = Constant::db_url;
        $db_username = Constant::db_username;
        $db_password = Constant::db_password;
        $db_name = Constant::db_name;
        
        $db_con = mysql_connect($db_url, $db_username, $db_password);
        
        if (!$db_con)
        {
            $error_array[0] = "Could not connect to database";
            return $error_array;
        }
        
        mysql_select_db($db_name, $db_con);
        
        //clear existing data
        $delete_sql_lesson = "delete from ct_lesson;";
        if (!mysql_query($delete_sql_lesson, $db_con))
        {
            $error_array[0] = "Fail to clear database. Please contact system admin";
            return $error_array;
        }
        $delete_sql_class = "delete from ct_class_matching;";
        if (!mysql_query($delete_sql_class, $db_con))
        {
            $error_array[0] = "Fail to clear database. Please contact system admin";
            return $error_array;
        }
        $delete_sql_teacher = "delete from ct_teacher_matching;";
        if (!mysql_query($delete_sql_teacher, $db_con))
        {
            $error_array[0] = "Fail to clear database. Please contact system admin";
            return $error_array;
        }
        
        if(!mysql_query($sql_insert_lesson))
        {
            $error_array[0] = "Error in ct_lesson table. Fail to insert timetable. Please try again later";
            return $error_array;
        }
        if(!mysql_query($sql_insert_lesson_class))
        {
            $error_array[0] = "Error in ct_class_matching table. Fail to insert timetable. Please try again later";
            return $error_array;
        }
        if(!mysql_query($sql_insert_lesson_teacher))
        {
            $error_array[0] = "Error in ct_teacher_matching table. Fail to insert timetable. Please try again later";
            return $error_array;
        }
        
        //print error info
        if(count($empty_subject)>0)
        {
            $err_message = "The following lessons have empty subject : <br><br>";
            foreach($empty_subject as $a_key)
            {
                $err_message .= " ".$a_key." ";
            }
            $err_message .= "<br><br>";
            $err_message .= "An invalid value is used as a replacement in database. This may cause unpredictable problems in future. Please check the timetable and timetable analyzer. Simply reload the timetable after problems are fixed.";
            $err_message .= "<br><br>";
            
            $error_array[1] = $err_message;
        }
        if(count($empty_days)>0)
        {
            $err_message = "The following lessons have empty day : <br><br>";
            foreach($empty_days as $a_key)
            {
                $err_message .= " ".$a_key." ";
            }
            $err_message .= "<br><br>";
            $err_message .= "An invalid value is used as a replacement in database. This may cause unpredictable problems in future. Please check the timetable and timetable analyzer. Simply reload the timetable after problems are fixed.";
            $err_message .= "<br><br>";
            
            $error_array[2] = $err_message;
        }
        if(count($empty_start_time)>0)
        {
            $err_message = "The following lessons have empty start time : <br><br>";
            foreach($empty_start_time as $a_key)
            {
                $err_message .= " ".$a_key." ";
            }
            $err_message .= "<br><br>";
            $err_message .= "An invalid value is used as a replacement in database. This may cause unpredictable problems in future. Please check the timetable and timetable analyzer. Simply reload the timetable after problems are fixed.";
            $err_message .= "<br><br>";
            
            $error_array[3] = $err_message;
        }
        if(count($empty_end_time)>0)
        {
            $err_message = "The following lessons have empty end time : <br><br>";
            foreach($empty_end_time as $a_key)
            {
                $err_message .= " ".$a_key." ";
            }
            $err_message .= "<br><br>";
            $err_message .= "An invalid value is used as a replacement in database. This may cause unpredictable problems in future. Please check the timetable and timetable analyzer. Simply reload the timetable after problems are fixed.";
            $err_message .= "<br><br>";
            
            $error_array[4] = $err_message;
        }
        if(count($empty_class)>0)
        {
            $err_message = "The following lessons have empty class : <br>";
            foreach($empty_class as $a_key)
            {
                $err_message .= " [ ".$a_key." ] ";
            }
            $err_message .= "<br>";
            $err_message .= "An invalid value is used as a replacement in database. This may cause unpredictable problems in future. Please check the timetable and timetable analyzer. Simply reload the timetable after problems are fixed.";
            $err_message .= "<br><br>";
            
            $error_array[5] = $err_message;
        }
        if(count($empty_teacher)>0)
        {
            $err_message = "The following abbreviation names do not exist in database : <br><br>";
            foreach($empty_teacher as $a_key)
            {
                $err_message .= " [ ".$a_key." ] ";
            }
            $err_message .= "<br><br>";
            $err_message .= "We could not store teaching information of these teachers in database. This may cause severe problems during scheduling. Please contact the system admin immediately. Simply reload the timetable after problems are fixed.";
            $err_message .= "<br><br>";
            
            $error_array[6] = $err_message;
        }
        
        return $error_array;
    }
    
    /**
     * 
     * @param type $accname - accname of leave teacher or ""
     * @param type $class - standard class name or ""
     * @param string $date "yyyy-mm-dd"
     * @return Complex data structure if succeed. null if fail.
     */
    public static function getReliefTimetable($accname, $class, $date)
    {
        $normal_dict = Teacher::getAllTeachers();
        $temp_dict = Teacher::getTempTeacher("");
        
        $db_con = Constant::connect_to_db('ntu');
        
        if(empty($db_con))
        {
            return null;
        }
        
        if(empty($accname) && empty($class))
        {
            $sql_query_relief = "select * from ct_lesson, rs_relief_info, ct_class_matching where ct_lesson.lesson_id = rs_relief_info.lesson_id and ct_lesson.lesson_id = ct_class_matching.lesson_id and rs_relief_info.date = DATE('".mysql_real_escape_string(trim($date))."');";
        }
        else if(empty($accname) && !empty($class))
        {
            $sql_query_relief = "select * from ct_lesson, rs_relief_info, ct_class_matching where ct_lesson.lesson_id = rs_relief_info.lesson_id and ct_lesson.lesson_id = ct_class_matching.lesson_id and rs_relief_info.date = DATE('".mysql_real_escape_string(trim($date))."') and ct_class_matching.class_name = '".mysql_real_escape_string(trim($class))."';";
        }
        else if(!empty($accname) && empty($class))
        {
            $sql_query_relief = "select * from ct_lesson, rs_relief_info, ct_class_matching where ct_lesson.lesson_id = rs_relief_info.lesson_id and ct_lesson.lesson_id = ct_class_matching.lesson_id and rs_relief_info.date = DATE('".mysql_real_escape_string(trim($date))."') and rs_relief_info.leave_teacher = '".mysql_real_escape_string(trim($accname))."';";
        }
        else
        {
            $sql_query_relief = "select * from ct_lesson, rs_relief_info, ct_class_matching where ct_lesson.lesson_id = rs_relief_info.lesson_id and ct_lesson.lesson_id = ct_class_matching.lesson_id and rs_relief_info.date = DATE('".mysql_real_escape_string(trim($date))."') and ct_class_matching.class_name = '".mysql_real_escape_string(trim($class))."' and rs_relief_info.leave_teacher = '".mysql_real_escape_string(trim($accname))."';";
        }
        
        $query_relief_result = mysql_query($sql_query_relief);
        if(!$query_relief_result)
        {
            return null;
        }
        
        $result = Array();
        
        while($row = mysql_fetch_assoc($query_relief_result))
        {
            $start_time_index = $row['start_time_index']-1;
            $end_time_index = $row['end_time_index']-1;
            
            for($i = $start_time_index; $i<$end_time_index; $i++)
            {
                if(empty($result[$i]))
                {
                    $result[$i] = Array();
                }

                $leave_teacher_id = $row['leave_teacher'];
                $relief_teacher_id = $row['relief_teacher'];
                $subject = $row['subj_code'];
                $venue = empty($row['venue'])?"":$row['venue'];
                
                $existed = false;
                
                for($j=0; $j<count($result[$i]);$j++)
                {
                    if(empty($result[$i][$j]))
                    {
                        continue;
                    }
                    
                    if(strcmp($result[$i][$j]['subject'], $subject)===0 && strcmp($result[$i][$j]['teacher-accname'], $leave_teacher_id)===0 && strcmp($result[$i][$j]['relief-teacher-accname'], $relief_teacher_id)===0 && strcmp($result[$i][$j]['venue'], $venue)===0)
                    {
                        $existed = true;
                        $result[$i][$j]['class'][] = $row['class_name'];
                        break;
                    }
                }
                
                if(!$existed)
                {
                    if(array_key_exists($leave_teacher_id, $normal_dict))
                    {
                        $leave_name = $normal_dict[$leave_teacher_id]['name'];
                    }
                    else
                    {
                        $leave_name = "";
                    }

                    if(array_key_exists($relief_teacher_id, $temp_dict))
                    {
                        $relief_name = $temp_dict[$relief_teacher_id]['fullname'];
                    }
                    else if(array_key_exists($relief_teacher_id, $normal_dict))
                    {
                        $relief_name = $normal_dict[$relief_teacher_id]['name'];
                    }
                    else
                    {
                        $relief_name = "";
                    }

                    $new_teaching = Array(
                        'subject' => $subject,
                        'venue' => $venue,
                        'teacher-accname' => $leave_teacher_id,
                        'teacher-fullname' => $leave_name,
                        'relief-teacher-accname' => $relief_teacher_id,
                        'relief-teacher-fullname' => $relief_name,
                        'class' => Array($row['class_name'])
                    );
                    
                    $result[$i][] = $new_teaching;
                }
            }
        }
        
        return $result;
    }
    
    private static function generateLessonPK($type, $year, $sem, $weekday, $start_time, $end_time, $class_obj_list, $teacher_obj_list)
    {
        if(count($class_obj_list) === 0)
        {
            $class_short = 'emp';
        }
        else
        {
            $class_list = array_keys($class_obj_list);
            
            if(count($class_list) > 1)
            {
                sort($class_list);
            }
            
            $first_class = $class_list[0];
            
            $break_class_name = explode(" ", $first_class);
            
            if(count($break_class_name) === 1)
            {
                $class_short = $break_class_name[0];
            }
            else
            {
                if(strlen($break_class_name[0]) > 1)
                {
                    $class_short = substr($break_class_name[0], 0, 2).substr($break_class_name[1], 0, 1);
                }
                else if(strlen($break_class_name[1]) > 1)
                {
                    $class_short = $break_class_name[0].substr($break_class_name[1], 0, 2);
                }
                else
                {
                    $class_short = $break_class_name[0].$break_class_name[1];
                }
            }
        }
        
        if(count($teacher_obj_list) === 0)
        {
            $teacher_short = 'emp';
        }
        else
        {
            $teacher_list = array_keys($teacher_obj_list);
            
            if(count($teacher_list) > 1)
            {
                sort($teacher_list);
            }
            
            $first_teacher = $teacher_list[0];
            
            $break_teacher_name = explode(" ", $first_teacher);
            
            if(count($break_teacher_name) === 1)
            {
                if(strlen($break_teacher_name[0])>=3)
                {
                    $teacher_short = substr($break_teacher_name[0], 0, 3);
                }
                else
                {
                    $teacher_short = $break_teacher_name[0].rand(0, 99);
                }
                
            }
            else
            {
                if(strlen($break_teacher_name[0]) > 1)
                {
                    $teacher_short = substr($break_teacher_name[0], 0, 2).substr($break_teacher_name[1], 0, 1);
                }
                else if(strlen($break_teacher_name[1]) > 1)
                {
                    $teacher_short = $break_teacher_name[0].substr($break_teacher_name[1], 0, 2);
                }
                else
                {
                    $teacher_short = $break_teacher_name[0].$break_teacher_name[1];
                }
            }
        }
        
        return $type.$year.$sem.$weekday.$start_time.$end_time.$class_short.$teacher_short;
    }
}
?>
