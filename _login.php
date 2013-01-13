<?php
//initialize session
session_start();
include('class/User.php');

//call function to verify username and password
$result = User::login($_POST['username'], $_POST['password']);

//true if user name does or password does not match
$_session["loginError"] = False;
//page to be redirected to
$destination = "index.php";

switch($result){
    case "":        
        $_session["loginError"] = True;
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
