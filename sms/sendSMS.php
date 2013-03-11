<?php
/**
 * input : $_REQUEST['sms_input'] =
 * {
 *      "date" => date
 *      "input" =>
 *      [
 *          {"phoneNum" => ..., "name" => ..., "accName" => ..., "message" => ..., "type" => ...},
 *          {...},
 *      ]
 * }
 */
spl_autoload_register(function($class){
    require_once dirname(__FILE__)."/../class/$class.php";
});

$options = getopt("s:");
$input_str = $options["s"];
$input_str_trim = substr($input_str, 1, strlen($input_str) - 2);

session_id($input_str_trim);
session_start();

$input = $_SESSION["sms"];
unset($_SESSION['sms']);
session_write_close();

$sms_input = $input['input'];
$date = $input['date'];

SMS::sendSMS($sms_input, $date);

?>
