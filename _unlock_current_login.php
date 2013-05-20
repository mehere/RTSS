<?php
spl_autoload_register(function($class){
    require_once "class/$class.php";
});

Template::validate(true, true);

$output=array('error' => 1);

$area=$_GET['area'];

if ($area == 'SCHEDULER' || $area == 'EDIT_SCHEDULE')
{
    User::unlock($_SESSION['accname'], $area);
    $output=array('error' => 0);
}

header('Content-type: application/json');
echo json_encode($output);
?>
