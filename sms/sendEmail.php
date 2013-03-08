<?php
require_once dirname(__FILE__).'/../class/email_lib/swift_required.php';
spl_autoload_register(function($class){
    require_once dirname(__FILE__)."/../class/$class.php";
});

$options = getopt("s:");
$input_str = $options["s"];
$input_str_trim = substr($input_str, 1, strlen($input_str) - 2);

session_id($input_str_trim);
session_start();

$input = $_SESSION['email'];
unset($_SESSION['email']);

$from = $input['from'];
$to = $input['to'];

Email::sendMail($from, $to);
?>
