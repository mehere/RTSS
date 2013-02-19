<?php
require_once 'constant.php';

session_start();

if (!$_SESSION['accname'] || (!$BYPASS_ADMIN && $_SESSION['type'] != 'admin'))
{
    header("Location: /RTSS/");
}
?>