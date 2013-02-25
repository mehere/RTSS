<?php
header("Expires: 0");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");

require_once '../../php-head.php';

require_once '../../class/SchedulerDB.php';
require_once '../../class/SMSDB.php';

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

$output['display']= <<< EOD
<table class="table-info">
	<thead>
        <tr><th>Name</th><th>SMS</th><th>Email</th></tr>
    </thead>
    <tbody>
        $trStr
    </tbody>
</table>
EOD;

unset($_SESSION['scheduleIndex']);
unset($_SESSION['excluded']);

header('Content-type: application/json');
echo json_encode($output);
?>
