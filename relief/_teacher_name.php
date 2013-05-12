<?php
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

Template::validate(true, true);

$type=$_GET['type'];
$accname=$_GET['accname'];

$output=array();
if ($accname)
{
    $teacherContactList=Teacher::getTeacherContact();
    $output=$teacherContactList[$accname];
}
else
{
    $output=Teacher::getTeacherName($type);
}

header('Content-type: application/json');
echo json_encode($output);
?>
