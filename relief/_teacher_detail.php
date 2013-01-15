<?php
header("Expires: 0");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");

//initialize session
session_start();
include('../class/Teacher.php');
$login = true;

//check if user is logged in
if (isset($_SESSION['accname']))
{
    if (!$_SESSION['accname']) {
        $login = false;
    }
}else
    $login = false;

if (!$login) {
    //indicates error
    header('Content-type: application/json');
    $output = array("error" => "Please login first.");
    echo json_encode($output);
    exit;
}else
{
    //indicates no error
    $output['error'] = "";
}

//retrieve account name of the selected entry
$accname =$_GET['accname'];
$output = Teacher::getIndividualTeacherDetail($accname);

//Prepare teacher information for display
$id = $output['ID'];
$name = $output['name'];
$gender = $output['gender'];
$mobile = $output['mobile'];
$email = $output['email'];

json_encode($output);

?>
<table class="table-info">                        
    <tbody>
        <tr><td>ID:</td><td><?php echo $id; ?></td></tr>
        <tr><td>Name:</td><td><?php echo $name; ?></td></tr>
        <tr><td>Gender:</td><td><?php echo $gender; ?></td></tr>
        <tr><td>Mobile:</td><td><?php echo $mobile; ?></td></tr>
        <tr><td>Email:</td><td><?php echo $email; ?></td></tr>        
    </tbody>
</table>