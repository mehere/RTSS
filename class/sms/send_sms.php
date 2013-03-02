<?php

//include_once ("common.php");
header("Expires: 0");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");

include('../SMSDB.php');

function sendSMS($receiverList, $scheduleDate) {
    date_default_timezone_set('Asia/Singapore');
    set_time_limit(1200);
    $index = 0;
    $attempt = 1;
    while ($index < sizeof($receiverList)) {
        if ($attempt == 1) {
            $phoneNum = $receiverList[$index]["phoneNum"];
            if (strlen(trim($phoneNum)) == 8) {
                $phoneNum = "+65" . $phoneNum;
            }
            $name = $receiverList[$index]["name"];
            $message = "<iScheduler> Dear $name, " . $receiverList[$index]["message"];
            $timeCreated = date('Y-m-d H:i:s');
            $accname = $receiverList[$index]["accname"];
            $msgRecord = array("phoneNum" => $phoneNum, "timeCreated" => $timeCreated, "accName" => $accname);
            $smsId = SMSDB::storeSMSout($msgRecord, $scheduleDate);
            $message = $message . "Please reply in the following format: '$smsId-Yes' for acceptance or '$smsId-no' for decline.";
        }
        if (trim($phoneNum)) {
            chdir('C:\xampp\htdocs\fscan\sms');
            $command = 'java -jar vigsyssmscom4.jar "1" "' . $phoneNum . '" "' . $message . '"';
            $apiOutput = shell_exec($command . "\n");
            $outputCode = substr($apiOutput, strlen($apiOutput) - 3, 3);
        } else {
            $outputCode = 104;
            $attemp = 3;
        }        
        $status = mapCode($outputCode);        
        if ($outputCode == "100") {
            $sendingResult[$index] = array("phoneNum" => $phoneNum, "name" => $name, "message" => $message, "status" => $status, "accname" => $accname);
            $updateComponent = array("smsId" => $smsId, "message" => $message, "timeSent" => $timeCreated, "status" => $status);
            SMSDB::updateSMSout($updateComponent);
            $index++;
            $attempt = 1;
        } else if ($attempt >= 3) {
            $sendingResult[$index] = array("phoneNum" => $phoneNum, "name" => $name, "message" => $message, "status" => $status, "accname" => $accname);
            $updateComponent = array("smsId" => $smsId, "message" => $message, "timeSent" => "", "status" => $status);
            SMSDB::updateSMSout($updateComponent);
            $index++;
            $attempt = 1;
        } else {
            $attempt++;
        }
    }
    return $sendingResult;
}

function mapCode($code) {
    switch ($code) {
        case 100:
            return "OK";
            break;
        case 101:
            return "Invalid serial no (Not a VigSys VM10 model)";
            break;
        case 102:
            return "Corrupted ccyk file";
            break;
        case 103:
            return "Invalid parameter";
            break;
        case 104:
            return "Invalid Phone number";
            break;
        case 105:
            return "Invalid message length";
            break;
        case 106:
            return "Communication problem";
            break;
        case 107:
            return "Invalid option";
            break;
        default:
            return "Unable to connect to SMS server";
    }
}

?>
