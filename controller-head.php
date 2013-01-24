<?php 
session_start();

if (!$_SESSION['accname']) 
{
    header('Content-type: application/json');
    echo json_encode(array('error'=>1));
    return;
}
?>
