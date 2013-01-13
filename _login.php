<?php
header("Expires: 0");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");

//initialize session
session_start();
include('class/User.php');

//true if user name does or password does not match
$_session["loginError"] = false;
//page to be redirected to
$destination = "index.php";

//call function to verify username and password
$_session["userType"] = User::login($_POST['username'], $_POST['password']);

switch($_session["userType"]){
    case "":        
        $_session["loginError"] = true;
        break;
    case "teacher":
        $destination = "/RTSS/relief/index.php";
        break;
    case "admin":
        $destination = "/RTSS/timetable/index.php";
        break;
}

header("Location: $destination");

?>
