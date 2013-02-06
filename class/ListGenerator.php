<?php

require_once 'util.php';
require_once 'Teacher.php';

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class ListGenerator
{
    public static function getTeacherName($date)
    {
        $result = Array();
        $teacher_dict = Teacher::getAllTeachers();
        
        //convert date to weekday
        $weekday_string = date("D",  strtotime($date));
        
        $weekday_number = 1;
        
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
                return $result;
            }
        }
        
        //connect to db
        $db_con = Constant::connect_to_db('ntu');
        
        if(empty($db_con))
        {
            return $result;
        }
        
        $sql_query_teacher = "select distinct ct_teacher_matching.teacher_id from ct_lesson, ct_teacher_matching where ct_lesson.lesson_id = ct_teacher_matching.lesson_id and ct_lesson.weekday = ".$weekday_number.";";
        $query_teacher_result = mysql_query($sql_query_teacher);
        if(!$query_teacher_result)
        {
            return $result;
        }
        
        while($row = mysql_fetch_assoc($query_teacher_result))
        {
            if(array_key_exists($row['teacher_id'], $teacher_dict))
            {
                $fullname = $teacher_dict[$row['teacher_id']]['name'];
            }
            else
            {
                $fullname = "";
            }
            
            $result[$row['teacher_id']] = $fullname;
        }
        
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
        
        //convert date to weekday
        $weekday_string = date("D",  strtotime($date));
        
        $weekday_number = 1;
        
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
                return $result;
            }
        }
        
        $sql_query_class = "select distinct ct_class_matching.class_name from ct_lesson, ct_class_matching where ct_lesson.lesson_id = ct_class_matching.lesson_id and ct_lesson.weekday = ".$weekday_number.";";
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
