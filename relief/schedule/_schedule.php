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

$format = "Y-m-d";
$dateScheduled = DateTime::createFromFormat($format, $dateScheduledString);
/* @var $dateScheduled DateTime */
$day = $dateScheduled->format("N");
//echo "<br>Day: $day";

?>
