<?php 
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

Template::validate(true, true);

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
        $classInfo=array('class'=>explode(';', $_POST["class-$i"]));
        foreach ($keyArr as $keyEntry)
        {
            $classInfo[$keyEntry]=trim($_POST[$keyEntry."-$i"]);
        }
        $timetable[]=$classInfo;
    }
    
    $info=array('accname'=>trim($_POST['accname']));
    $info['speicalty']=explode(',', $_POST['specialty']);
    
    if (!TimetableDB::uploadAEDTimetable($timetable, $info, $_POST['year'], $_POST['sem']))
    {
        $output['error']=1;
    }
}

header('Content-type: application/json');
echo json_encode($output);
?>
