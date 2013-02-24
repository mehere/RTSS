<?php
header("Expires: 0");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");

require_once '../../controller-head.php';

require_once '../../class/SchedulerDB.php';

$output=array('error'=>0);

header('Content-type: application/json');
echo json_encode($output);
?>
