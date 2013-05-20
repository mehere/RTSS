<?php
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

Template::validate(true, true);

$output=array('error' => 0);

$excludeClassNum=$_GET['exclude-class-num'];
if ($excludeClassNum)
{
    $escapedClassArr=array();
    for ($i=0; $i<$excludeClassNum; $i++)
    {
        if ($_GET["class-select-$i"])
        {
            $escapedClassArr[]=array(
                'teacher_id' => $_GET["teacher-accname-$i"],
                'type' => $_GET["type-$i"],
                'start_time' => SchoolTime::getTimeIndex($_GET["start-time-$i"]),
                'end_time' => SchoolTime::getTimeIndex($_GET["end-time-$i"])
            );
        }
    }
    
//    $output['error']=var_export($escapedClassArr, true);
    
    if ($escapedClassArr)
    {        
        if (!SchedulerDB::passEscapedLessons($escapedClassArr))
        {
            $output['error']=1;
        }
    }
}

header('Content-type: application/json');
echo json_encode($output);
?>
