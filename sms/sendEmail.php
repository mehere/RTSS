<?php
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

$input_str = $_REQUEST['sms_input'];
$input = unserialize($input_str);

$sms_input = $input['input'];
$date = $input['date'];

SMS::sendSMS($sms_input, $date);
?>
