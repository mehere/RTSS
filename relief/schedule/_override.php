<?php
header("Expires: 0");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");

require_once '../../controller-head.php';
require_once '../../constant.php';

require_once '../../class/TimetableDB.php';
require_once '../../class/SchedulerDB.php';

$output=array('hasConflict'=>TimetableDB::checkTimetableConflict($_GET['scheduleIndex'], 
        array($_GET['timeStart'], $_GET['timeEnd']), $_GET['reliefAccName'], $_SESSION['scheduleDate'], $_GET['lessonID']));
$output['overridenFail']=0;

$output['error']=var_export($_GET, true);

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