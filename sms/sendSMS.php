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
    require_once "../class/$class.php";
});

$options = getopt("string:");
$input_str = $options["string"];
$input_str = substr($input_str, 1, strlen($input_str) - 1);
error_log($input_str);
$input = unserialize($input_str);

$sms_input = $input['input'];
$date = $input['date'];

SMS::sendSMS($sms_input, $date);

?>
