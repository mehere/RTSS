<?php
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

//Template::validate(true, true);

$info=$_GET['info'];
$sem=$_GET['sem'];
$year=$_GET['year'];

$output=array();

if (!$info || !$sem || !$year)
{
    $output['error']=1;
}

switch ($info)
{
    case 'class':
        $output=StaticInfoDB::getAllClasses($sem, $year);
        break;
    case 'subject':
        $output=StaticInfoDB::getAllSubjects($sem, $year);
}

header('Content-type: application/json');
echo json_encode($output);
?>
