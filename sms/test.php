<?php
header("Expires: 0");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");

//initialize session
session_start();

include('send_sms.php');

for($i = 0; $i < 5; $i++){
    $receiverList = array( "name" => "Virgil",
                       "phoneNum" => "+6597394731",
                       "message" => "integration test $i",
                    );
}

$scheduleDate = "13/02/25";

$_SESSION['accname'] = "ryujicai";

sendSMS($receiverList, $scheduleDate)

?>
