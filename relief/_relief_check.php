<?php 
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

Template::validate(true, true);

$output=array('areDatesWithinSem' => SchoolTime::checkDatesInSameSem(new DateTime($_GET['date-from']), new DateTime($_GET['date-to'])));

header('Content-type: application/json');
echo json_encode($output);
?>
