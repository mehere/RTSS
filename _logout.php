<?php
header("Expires: 0");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");

//initialize session
session_start();

//log out only if user is currently logged in
if ($_SESSION['accname']){
    //destroy session
    session_destroy();
}
header("Location: /RTSS/");
?>
