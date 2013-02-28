<?php

require_once 'util.php';
require_once 'Teacher.php';

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class ListGenerator
{
    public static function getTeacherName($date, $scheduleIndex=-1)
    {
        $result = Array();
        $teacher_dict = Teacher::getAllTeachers();  
        $temp_dict = Teacher::getTempTeacher("");
        
        //connect to db
        $db_con = Constant::connect_to_db('ntu');
        
        if(empty($db_con))
        {
            return $result;
        }
        
        $date_obj = new DateTime($date);
        $weekday = $date_obj->format('N');
        
        //$sql_query_teacher = "select distinct ct_teacher_matching.teacher_id from rs_relief_info, ct_lesson, ct_teacher_matching where ct_lesson.lesson_id = ct_teacher_matching.lesson_id and ct_lesson.lesson_id = rs_relief_info.lesson_id and rs_relief_info.date = ".$date_str.";";
        $sql_query_normal = "select distinct ct_teacher_matching.teacher_id from ct_lesson, ct_teacher_matching where ct_lesson.lesson_id = ct_teacher_matching.lesson_id and ct_lesson.weekday = ".$weekday.";";
        
        $query_normal_result = Constant::sql_execute($db_con, $sql_query_normal);
        if(is_null($query_normal_result))
        {
            return $result;
        }
        
        foreach($query_normal_result as $row)
        {
            $accname = $row['teacher_id'];
            
            if(array_key_exists($accname, $teacher_dict))
            {
                $fullname = $teacher_dict[$accname]['name'];
            }
            else if(array_key_exists($accname, $temp_dict))
            {
                $fullname = $temp_dict[$accname]['fullname'];
            }
            else
            {
                $fullname = $accname;
            }
            
            $result[$accname] = $fullname;
        }
        
        //relief teachers
        if($scheduleIndex === -1)
        {
            $sql_query_teacher = "select distinct relief_teacher from rs_relief_info where DATE(date) = '$date';";
        }
        else
        {
            $sql_query_teacher = "select distinct relief_teacher from temp_each_alternative where schedule_id = ".$scheduleIndex.";";
        }
        
        $query_teacher_result = Constant::sql_execute($db_con, $sql_query_teacher);
        if(is_null($query_teacher_result))
        {
            return $result;
        }
        
        foreach($query_teacher_result as $row)
        {
            $accname = $row['relief_teacher'];
            
            if(array_key_exists($accname, $teacher_dict))
            {
                $fullname = $teacher_dict[$accname]['name'];
            }
            else if(array_key_exists($accname, $temp_dict))
            {
                $fullname = $temp_dict[$accname]['fullname'];
            }
            else
            {
                $fullname = $accname;
            }
            
            $result[$accname] = $fullname;
        }
        
        asort($result);
        
        return $result;
    }
    
    public static function getClassName($date)
    {
        $result = Array();
        
        //connect to db
        $db_con = Constant::connect_to_db('ntu');
        
        if(empty($db_con))
        {
            return $result;
        }
        
        $sql_query_class = "select distinct ct_class_matching.class_name from rs_relief_info, ct_class_matching where rs_relief_info.lesson_id = ct_class_matching.lesson_id and DATE(rs_relief_info.date) = '$date';";
        $query_class_result = mysql_query($sql_query_class);
        if(!$query_class_result)
        {
            return $result;
        }
        
        while($row = mysql_fetch_assoc($query_class_result))
        {
            $result[] = $row['class_name'];
        }
        
        return $result;
    }
    
    public static function getTeacherType()
    {
        $result = Array();
        
        $db_con = Constant::connect_to_db('ifins');
        
        if(empty($db_con))
        {
            return $result;
        }
        
        $sql_query_type = "select dept_name from actatek_dept where dept_desc = 'Teacher' or dept_desc = 'Admin' or dept_desc is NULL";
        $query_type_result = mysql_query($sql_query_type);
        
        if(!$query_type_result)
        {
            return $result;
        }
        
        while($row = mysql_fetch_assoc($query_type_result))
        {
            $result[] = $row['dept_name'];
        }
        
        return $result;
    }
}
?>
