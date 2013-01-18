<?php
header("Expires: 0");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");

//initialize session
session_start();
include('../class/Teacher.php');
$login = true;

//check if user is logged in
if (!$_SESSION['accname']) {
    $login = false;
}

if (!$login) {
    //indicates error
    require_once '../constant.php';
    die(PageConstant::$ERROR_TEXT['login']['loginFirst']);
}else
{
    //indicates no error
    $output['error'] = "";
}

//retrieve account name of the selected entry
$accname =$_GET['accname'];
$output = Teacher::getIndividualTeacherDetail($accname);

//Prepare teacher information for display
?>
<table class="table-info">                        
    <tbody>
        <tr><td>Account ID:</td><td><?php echo $output['ID']; ?></td></tr>
        <tr><td>Name:</td><td><?php echo $output['name']; ?></td></tr>
        <tr><td>Gender:</td><td><?php echo $output['gender']; ?></td></tr>
        <tr><td>Handphone:</td><td><?php echo $output['mobile']; ?></td></tr>
        <tr><td>Email:</td><td><?php echo $output['email']; ?></td></tr>        
    </tbody>
</table>