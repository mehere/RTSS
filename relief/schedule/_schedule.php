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
$day = $dateScheduled->format("N");
//echo "<br>Day: $day";
$scheduler = new SchedulerDB($dateScheduled);
$scheduleTempTeachers = $scheduler->getTempTeachers();
$scheduleAedTeachers = $scheduler->getAEDLessonsToday();
$scheduleUntrainedTeachers = $scheduler->getUntrainedTeachers();
$scheduleNormTeachers = $scheduler->getNormLessonsToday();
$scheduleLeaves = $scheduler->getLeave();

$resultTempTeachers = $scheduleTempTeachers["success"];
$resultAedTeachers = $scheduleAedTeachers["success"];
$resultUntrainedTeachers = $scheduleUntrainedTeachers["success"];
$resultNormTeachers = $scheduleNormTeachers["success"];
$arrLeave = $scheduleLeaves["success"];

// if error
if (!($resultTempTeachers && $resultAedTeachers && $resultUntrainedTeachers
        && $resultNormTeachers))
{
    $errorMsg = "";
    if (isset($resultTempTeachers["error_msg"]))
    {
        $errorMsg = $errorMsg . $resultTempTeachers["error_msg"] . '<br>';
    }
    if (isset($resultAedTeachers["error_msg"]))
    {
        $errorMsg = $errorMsg . $resultAedTeachers["error_msg"] . '<br>';
    }
    if (isset($resultUntrainedTeachers["error_msg"]))
    {
        $errorMsg = $errorMsg . $resultUntrainedTeachers["error_msg"] . '<br>';
    }
    if (isset($resultNormTeachers["error_msg"]))
    {
        $errorMsg = $errorMsg . $resultNormTeachers["error_msg"] . '<br>';
    }

    echo "An erro has occured: $errorMsg";
    exit();
}

$tempTeachers = $scheduleTempTeachers["teachers"];
$aedTeachers = $scheduleAedTeachers["teachers"];
$untrainedTeachers = $scheduleUntrainedTeachers["teachers"];
$normTeachers = $scheduleNormTeachers["teachers"];

$arrTempCTeachers = array();
foreach ($tempTeachers as $aTeacher){
    $aCompactTeacher = new TeacherCompact($aTeacher,"Temp");
    $arrTempCTeachers[$aCompactTeacher->accname] = $aCompactTeacher;
}

$arrAedCTeachers = array();
foreach ($aedTeachers as $aTeacher){
    $aCompactTeacher = new TeacherCompact($aTeacher,"Aed");
    $arrAedCTeachers[$aCompactTeacher->accname] = $aCompactTeacher;
}
$arrAedLeave = $arrLeave["Aed"];
foreach ($arrAedLeave as $accname => $leaveRecords){
    $arrAedCTeachers[$accname]->onLeave($leaveRecord);
}

foreach ($arrAedTeachers as $key => $value)
{

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
