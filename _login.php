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
$destination = "/RTSS/index.php";

//call function to verify username and password and store user type
$_session["type"] = User::login($_POST['username'], $_POST['password']);

//based on user type, decide where to redirect or simply go back with error message
if($_session["type"]){
    //store user name
    $_session["accname"] = $_POST['username'];
    if($_session["type"] == "admin")
        $destination = "/RTSS/relief/index.php";
    else
        $destination = "/RTSS/timetable/index.php";
}
else{
    $_session["loginError"] = true;
}

//redirect
header("Location: $destination");

?>
