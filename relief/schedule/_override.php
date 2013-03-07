<?php
spl_autoload_register(function($class){
    require_once "../../class/$class.php";
});

Template::validate(true, true);

$output=array('hasConflict'=>TimetableDB::checkTimetableConflict($_GET['scheduleIndex'], 
        array($_GET['timeStart'], $_GET['timeEnd']), $_GET['reliefAccName'], $_SESSION['scheduleDate'], $_GET['lessonID']));
$output['overridenFail']=0;

if ($output['hasConflict'] == 0)
{    
    if (!SchedulerDB::override($_GET['scheduleIndex'], $_GET['lessonID'], $_GET['teacherAccName'], $_GET['reliefAccName']))
    {
        $output['overridenFail']=1;
    }
}

header('Content-type: application/json');
echo json_encode($output);
?>