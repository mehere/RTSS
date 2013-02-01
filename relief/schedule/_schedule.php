<?php

function isAvailable($aTeacher){
    /* @var $aTeacher TeacherCompact */
    return $aTeacher->isAvailable();
}

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
const MAX_LESSON = 14;

$dateScheduledString = $_POST["date"];
$typeSchedule;
if (array_key_exists("btnScheduleAll", $_POST))
{
    $typeSchedule = 1;
} else if (array_key_exists("btnScheduleAdhoc", $_POST))
{
    $typeSchedule = 2;
}

echo "<br>type: $typeSchedule";

/// To-DO: format to be soft-coded
$format = "Y-m-d";
$dateScheduled = DateTime::createFromFormat($format, $dateScheduledString);
/* @var $dateScheduled DateTime */
$day = $dateScheduled->format("N");
//echo "<br>Day: $day";
//$teachers = Teacher::getTeachersLessons($day);
$t1 = new Teacher("t1");
$t2 = new Teacher("t2");
$teachers = array("t1" => $t1, "t2" => $t2);

$teachersAvailable = array();
$lessonsNeedRelief = array();

foreach ($teachers as $aTeacher)
{
    /* @var $aTeacher Teacher */
    $aCompactTeacher = new TeacherCompact($aTeacher);
    $someLessonsNeedRelief = $aCompactTeacher->needRelief($aTeacher);
    $lessonsNeedRelief = array_merge($lessonsNeedRelief,$someLessonsNeedRelief);
}

$teachersAvailable = array_filter($lessonsNeedRelief,"isAvailable");




?>
