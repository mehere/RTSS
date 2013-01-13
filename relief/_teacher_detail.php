<?php
header("Expires: 0");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");

//initialize session
session_start();
include('../class/Teacher.php');

//retrieve account name of the selected entry
$accname =$_GET['accname'];
//$teacherInfo = Teacher::getIndividualTeacherDetail($accname);

//test info
if ($accname == "caiVir")
    $teacherInfo = array("ID"=>"caiVir", "name"=>"Virgil Cai", "gender"=>"Male", "mobile"=>"97394731", "email"=>"ryujicai@hotmail.com");
else if($accname == "jieXu")
    $teacherInfo = array("ID"=>"jieXu", "name"=>"Xu Jie", "gender"=>"Female", "mobile"=>"92365504", "email"=>"xujie0086@gmail.com");
else
    $teacherInfo = array("ID"=>"sb", "name"=>"John Doe", "gender"=>"Male", "mobile"=>"98765432", "email"=>"johnDoe@hotmail.com");

//Prepare teacher information for display
$id = $teacherInfo["ID"];
$name = $teacherInfo["name"];
$gender = $teacherInfo["gender"];
$mobile = $teacherInfo["mobile"];
$email = $teacherInfo["email"];

json_encode($teacherInfo);

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