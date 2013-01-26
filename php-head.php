<?php
require_once 'constant.php';

session_start();

if (!$_SESSION['accname'])
{
    header("Location: /RTSS/");
}
?>