<?php
header("Expires: 0");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");

//initialize session
session_start();
include('../class/Teacher.php');

if (!$_session['accname']) 
{   
    header('Content-type: application/json');
    $output = array("error" => "Please login first.");
    echo json_encode($output);
    exit;
}
//retrieve account name of the selected entry
$accname =$_GET['accname'];
//$output = Teacher::getIndividualTeacherDetail($accname);

//test info
if ($accname == "caiVir")
    $output = array("ID"=>"caiVir", "name"=>"Virgil Cai", "gender"=>"Male", "mobile"=>"97394731", "email"=>"ryujicai@hotmail.com");
else if($accname == "jieXu")
    $output = array("ID"=>"jieXu", "name"=>"Xu Jie", "gender"=>"Female", "mobile"=>"92365504", "email"=>"xujie0086@gmail.com");
else
    $output = array("ID"=>"sb", "name"=>"John Doe", "gender"=>"Male", "mobile"=>"98765432", "email"=>"johnDoe@hotmail.com");

//Prepare teacher information for display
$id = $output["ID"];
$name = $output["name"];
$gender = $output["gender"];
$mobile = $output["mobile"];
$email = $output["email"];

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