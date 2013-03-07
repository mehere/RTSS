<?php
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

Template::validate(true, true);

$type=$_GET['type'];
header('Content-type: application/json');
echo json_encode(Teacher::getTeacherName($type));
?>
