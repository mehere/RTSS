<?php
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

$options = getopt("string:");
$input_str = $options["string"];
$input_str_trim = substr($input_str, 1, strlen($input_str) - 1);

$input = unserialize($input_str_trim);

$from = $input['from'];
$to = $input['to'];

Email::sendMail($from, $to);
?>
