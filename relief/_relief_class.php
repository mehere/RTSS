<?php
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

Template::validate(true, true);

$output=array('error' => 0);

$excludeClassNum=$_POST['exclude-class-num'];
if ($excludeClassNum)
{
    $escapedClassArr=array();
    for ($i=0; $i<$excludeClassNum; $i++);
    {
        if ($_POST["class-select-$i"])
        {
            $escapedClassArr[]=array(
                'teacher_id' => $_POST["teacher-accname-$i"],
                'type' => $_POST["type-$i"],
                'start_time' => $_POST["start-time-$i"],
                'end_time' => $_POST["end-time-$i"]
            );
        }
    }
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
