<?php

header("Expires: 0");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");

//initialize session
session_start();

include('send_sms.php');


$receiverList[0] = array("name" => "Virgil",
    "phoneNum" => "+6597394731",
    "message" => "integration test 1",
    "accname" => "ryujicai",
);
$receiverList[1] = array("name" => "Xu Jie",
    "phoneNum" => "+6592365504",
    "message" => "integration test 1",
    "accname" => "xujie0086",
);

$scheduleDate = "2013/02/24";

sendSMS($receiverList, $scheduleDate)
?>
