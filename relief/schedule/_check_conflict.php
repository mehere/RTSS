<?php
header("Expires: 0");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");

require_once '../../controller-head.php';

require_once '../../class/TimetableDB.php';

$output=array('hasConflict'=>TimetableDB::checkTimetableConflict($_GET['scheduleIndex'], 
        array($_GET['timeStart'], $_GET['timeEnd']), $_GET['reliefAccName'], $_SESSION['scheduleDate']));

$output['error']=var_export($_GET, true);

header('Content-type: application/json');
echo json_encode($output);
?>