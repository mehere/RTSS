<?php

require_once 'util.php';
require_once 'DayTime.php';
require_once 'Students.php';
require_once 'Teacher.php';

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Lesson
 *
 * @author Wee
 */
class Lesson {

    //put your code here

    public $teachers;
    public $classes;
    public $venue;
    public $subject;
    public $day;
    public $startTimeSlot;
    public $endTimeSlot;
    
    function __construct(DayTime $dayTime, $subject, $venue) {

        $this->teachers = array();
        $this->classes = array();
        $this->subject = $subject;
        $this->venue = $venue;

        $this->day = $dayTime->dayIndex;
        $this->startTimeSlot = $dayTime->timeIndex;
        $this->endTimeSlot = $this->startTimeSlot + 1;
    }



    function addClass(Students $aClass){
        $this->classes[$aClass->name] = $aClass;
    }

    function addTeacher(Teacher $aTeacher){
        $this->teachers[$aTeacher->abbreviation] = $aTeacher;
    }

    function incrementEndTime(){
        $this->endTimeSlot++;
    }

    //this function returns all normal teachers' lessons for a particular day, excluding AEDs'
    //input : string : date, can be in any php-supported format, e.g. yyyy-mm-dd. for more info, http://php.net/manual/en/datetime.formats.date.php
    //output : array {"success"->boolean, "error_msg"->string, “Lessons”-> arr{primaryKey -> Lesson}, “Teachers”->arr{primaryKey ->Teacher}}, pk of lesson is int, pk of teacher is accname string
    //note : please check output["success"] to see whether the query is successful. if fail, see output["error_msg"] for information; The returned teacher objects only contain accname and abbre name, email me if you need more information; The teacher objects is referenced by both Teachers list and lesson objects
    public static function getLessonsToday($date)
    {
        $result = Array(
            "success" => false,
            "error_msg" => NULL,
            "Lessons" => Array(),
            "Teachers" => Array()
        );
        
        //convert date to weekday
        $weekday_string = date("D",  strtotime($date));
        
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
        
        //lesson
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
             
             $result["Lessons"][$lesson_id] = $one_lesson;
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
            $the_lesson = $result["Lessons"][$row['lesson_id']];
            array_push($the_lesson->classes, $one_class); 
        }
        
        //teacher
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
            
            if(array_key_exists($acc_name, $result["Teachers"]))
            {
                $one_teacher = $result["Teachers"][$acc_name];
            }
            else
            {
                $abbreviation = $row['abbre_name'];
                $one_teacher = new Teacher($abbreviation);
                $one_teacher->accname = $acc_name;
                
                $result["Teachers"][$acc_name] = $one_teacher;
            }
            
            $the_lesson = $result["Lessons"][$row['lesson_id']];
            array_push($the_lesson->teachers, $one_teacher); 
        }
        
        $result["success"] = true;
        
        return $result;
    }
}

?>
