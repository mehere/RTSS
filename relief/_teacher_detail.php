<?php
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

Template::validate(true, true);

//retrieve account name of the selected entry
$accname=$_GET['accname'];
$output=array();
if (!$accname)
{
    $output['error']=2;    
}
else
{
    $teacherInfolist=Teacher::getIndividualTeacherDetail($accname);
    
    //Prepare teacher information for display
    $output['display']= <<< EOD
<table class="table-info">                        
    <tbody>
        <tr><td>Account ID:</td><td>{$teacherInfolist['ID']}</td></tr>
        <tr><td>Name:</td><td>{$teacherInfolist['name']}</td></tr>
        <tr><td>Gender:</td><td>{$teacherInfolist['gender']}</td></tr>
        <tr><td>Handphone:</td><td>{$teacherInfolist['handphone']}</td></tr>
        <tr><td>Email:</td><td><a href="mailto:{$teacherInfolist['email']}">{$teacherInfolist['email']}</a></td></tr>        
    </tbody>
</table>
EOD;
}

header('Content-type: application/json');
echo json_encode($output);
?>
