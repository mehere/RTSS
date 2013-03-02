<?php 
header("Expires: 0");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");

require_once '../controller-head.php';
require_once '../constant.php';
require_once '../class/TimetableDB.php';

$output=array();

$output['error']=0;
$accname=$_GET['accname'];
if (!$accname)
{
    $output['error']=1;
}
else
{
    $output['timetable']=TimetableDB::timetableForSem($accname, $_GET['year'], $_GET['sem']);
    PageConstant::escapeHTMLEntity($output['timetable']);
}

header('Content-type: application/json');
echo json_encode($output);
?>
