<?php



/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$dateScheduledString = $_POST["date"];
$typeSchedule;
if (array_key_exists("btnScheduleAll", $_POST))
{
    $typeSchedule = 1;
}
else if(array_key_exists("btnScheduleAdhoc", $_POST))
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
$teachers = array("t1"=>$t1,"t2"=> $t2);

$teachersOnLeave = array();
$teachersAvailable = array();

foreach ($teachers as $aTeacher){
    /* @var $aTeacher Teacher */
    $leaves = $aTeacher->leaves;
    $leavePeriods = array();
    foreach ($leaves as $leave){
        $startLeaveTime = $leave["startLeave"];
        $endLeaveTime = $leave["endLeave"];

        // convert to index;
       // $startLeaveIndex =
    }


    $aTimetable = $aTeacher->timetable;

    $busySlots = array_keys($aTeacher);


}


?>
