<?php 
header("Expires: 0");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");

require_once '../controller-head.php';
require_once '../constant.php';
require_once '../class/TimetableDB.php';

$output=array();

$output['error']=0;
$num=$_POST['num'];
if (!$num)
{
    $output['error']=1;
}
else
{
    for ($i=0; $i<$num; $i++)
    {
        
    }
    
    TimetableDB::uploadAEDTimetable($timetable);
}

header('Content-type: application/json');
echo json_encode($output);
?>
