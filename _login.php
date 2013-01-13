<?php
header("Expires: 0");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");

//initialize session
session_start();
include('class/User.php');

//true if user name does or password does not match
$_SESSION['loginError'] = false;
//page to be redirected to
$destination = "/RTSS/index.php";

//call function to verify username and password and store user type
$_SESSION['type'] = User::login($_POST['username'], $_POST['password']);

//based on user type, decide where to redirect or simply go back with error message
if($_SESSION['type']){
    //store user name
    $_SESSION['accname'] = $_POST['username'];
    if($_SESSION['type'] == "admin")
        $destination = "/RTSS/relief/index.php";
    else
        $destination = "/RTSS/timetable/index.php";
}
else{
    $_SESSION["loginError"] = true;
}

//redirect
header("Location: $destination");

?>
