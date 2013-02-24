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
$receiverList[1] = array("name" => "Virgil",
    "phoneNum" => "+659999999",
    "message" => "integration test 2",
    "accname" => "ryujicai",
);
$receiverList[2] = array("name" => "Virgil",
    "phoneNum" => "97394731",
    "message" => "integration test 2",
    "accname" => "ryujicai",
);
$receiverList[3] = array("name" => "Virgil",
    "phoneNum" => "   ",
    "message" => "integration test 3",
    "accname" => "ryujicai",
);
$receiverList[4] = array("name" => "Virgil",
    "phoneNum" => "",
    "message" => "integration test 4",
    "accname" => "ryujicai",
);



$scheduleDate = "2013/02/25";

sendSMS($receiverList, $scheduleDate)
?>
