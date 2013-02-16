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
    $timetable=array();
    $keyArr=array('day', 'time-from', 'time-to', 'subject', 'venue', 'isHighlighted');
    for ($i=0; $i<$num; $i++)
    {
        $classInfo=array('accname'=>$_POST['accname'], 'class'=>explode(';', $_POST["class-$i"]));
        foreach ($keyArr as $keyEntry)
        {
            $classInfo[$keyEntry]=trim($_POST[$keyEntry."-$i"]);
        }
        $timetable[]=$classInfo;
    }
    
//    $output['error']=var_export(array($timetable, $_POST['year'], $_POST['sem']), true);
    
    if (!TimetableDB::uploadAEDTimetable($timetable, $_POST['year'], $_POST['sem']))
    {
        $output['error']=1;
    }
}

header('Content-type: application/json');
echo json_encode($output);
?>
