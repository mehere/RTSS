<?php
spl_autoload_register(function($class){
    require_once "../../class/$class.php";
});

Template::validate(true, true);

$result=SchedulerDB::approve($_POST['schedule-index'], $_SESSION['scheduleDate']);

$trStr='';
foreach ($result as $key => $value)
{
    $smsSent=PageConstant::stateRepresent($value['smsSent']);
    $emailSent=PageConstant::stateRepresent($value['emailSent']);
    $trStr .= <<< EOD
<tr><td>{$value['fullname']}</td><td>$smsSent</td><td>$emailSent</td></tr>   
EOD;
}

$output=array('error' => 0);

/*
$output['display']= <<< EOD
<table class="table-info">
	<thead>
        <tr><th>Name</th><th style="width: 80px">SMS</th><th style="width: 80px">Email</th></tr>
    </thead>
    <tbody>
        $trStr
    </tbody>
</table>
EOD;
*/

$output['display']="The system is sending emails and SMSes to teachers affected. You can press 'OK' to leave this page.";

unset($_SESSION['scheduleType']);

header('Content-type: application/json');
echo json_encode($output);
?>