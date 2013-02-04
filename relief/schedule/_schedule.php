<?php

function isAvailable($aTeacher)
{
    /* @var $aTeacher TeacherCompact */
    return $aTeacher->isAvailable();
}

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

spl_autoload_register(
        function ($class)
        {
            include '../class/' . $class . '.php';
        });

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

//echo "<br>Day: $day";
$scheduler = new SchedulerDB($dateScheduled);
$arrTempTeachers = $scheduler->getTempTeachers();
$arrAedTeachers = $scheduler->getAedTeachers();
$arrUntrainedTeachers = $scheduler->getUntrainedTeachers();
$arrNormalTeachers = $scheduler->getNormalTeachers();
$arrLeaves = $scheduler->getLeave();

$arrTempCTeachers = array();
foreach ($arrTempTeachers as $aTeacher){
    $aCompactTeacher = new TeacherCompact($aTeacher,"Temp");
    $arrTempCTeachers[$aCompactTeacher->accname] = $aCompactTeacher;
}

$arrAedCTeachers = array();
foreach ($arrAedTeachers as $aTeacher){
    $aCompactTeacher = new TeacherCompact($aTeacher,"Aed");
    $arrAedCTeachers[$aCompactTeacher->accname] = $aCompactTeacher;
}
$arrAedLeave = $arrLeaves["Aed"];
foreach ($arrAedLeave as $accname => $leaveRecords){
    $arrAedCTeachers[$accname]->onLeave($leaveRecord);
}

$arrUntrainedCTeachers = array();
foreach ($arrUntrainedTeachers as $aTeacher){
    $aCompactTeacher = new TeacherCompact($aTeacher,"Untrained");
    $arrUntrainedCTeachers[$aCompactTeacher->accname] = $aCompactTeacher;
}
$arrUntrainedLeave = $arrLeaves["Untrained"];
foreach ($arrUntrainedLeave as $accname => $leaveRecords){
    $arrUntrainedCTeachers[$accname]->onLeave($leaveRecord);
}


$arrNormalCTeachers = array();
foreach ($arrUntrainedTeachers as $aTeacher){
    $aCompactTeacher = new TeacherCompact($aTeacher,"Untrained");
    $arrNormalCTeachers[$aCompactTeacher->accname] = $aCompactTeacher;
}
$arrNormalLeave = $arrLeaves["Untrained"];
foreach ($arrNormalLeave as $accname => $leaveRecords){
    $arrNormalCTeachers[$accname]->onLeave($leaveRecord);
}


$lessonsNeedRelief = array();
foreach ($normTeachers as $aTeacher)
{
    /* @var $aTeacher Teacher */
    $aCompactTeacher = new TeacherCompact($aTeacher, 'Norm');
    $someLessonsNeedRelief = $aCompactTeacher->onLeave($aTeacher);
    $lessonsNeedRelief = array_merge($lessonsNeedRelief, $someLessonsNeedRelief);
}
$normTeachersAvailable = array_filter($lessonsNeedRelief, "isAvailable");
?>
