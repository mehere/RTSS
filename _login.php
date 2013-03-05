<?php
spl_autoload_register(function($class){
    require_once "class/$class.php";
});

header("Expires: 0");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");

//initialize session
session_start();

//page to be redirected to
$destination = "/RTSS/index.php";

//call function to verify username and password and store user type
$userInfo = User::login(trim($_POST['username']), trim($_POST['password']));

$_SESSION['type'] = $userInfo['type'];

//based on user type, decide where to redirect or simply go back with error message
if ($_SESSION['type'])
{
    //store user name
    $_SESSION['username'] = $userInfo['fullname'];
    $_SESSION['accname'] = $userInfo['accname'];
    if ($_SESSION['type'] == "admin")
        $destination = "/RTSS/relief/index.php";
    else
        $destination = "/RTSS/timetable/index.php";
}
else
{
    $_SESSION["loginError"] = true;
}

//redirect
session_regenerate_id();
header("Location: $destination");
?>
