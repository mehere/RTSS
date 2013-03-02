<?php
//include_once ("common.php");
header("Expires: 0");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");

//initialize session
session_start();

//include('../class/SMSDB.php');

function sendSMS($receiverList, $scheduleDate) {    
    date_default_timezone_set('Asia/Singapore');
    set_time_limit(1200);    
    $index = 0;
    $attempt = 1;        
    while($index < sizeof($receiverList)){  	              
        $phoneNum = $receiverList[$index]["phoneNum"];                
        $name = $receiverList[$index]["name"];        
        $message = "<iScheduler> Dear $name, " . $receiverList[$index]["message"];     
        $timeCreated = date('Y-m-d H:i:s');
        $msgRecord = array("phoneNum" => $phoneNum, "name" => $name, "timeCreated" => $timeCreated, "accName" => $_SESSION['accname'], "scheduleDate" => $scheduleDate);
        $smsId = SMSDB::storeSMSout($msgRecord);
        $message = $message . "Please reply in the following format: '$smsId-Yes' for acceptance or '$smsId-no' for decline. Your response shall always start with the number given, which is your SMS conversation ID.";
        chdir('C:\xampp\htdocs\fscan\sms');
        $command = 'java -jar vigsyssmscom4.jar "1" "' . $phoneNum .'" "' . $message .'"';        
        $apiOutput = shell_exec($command."\n");           
        $outputCode = substr($apiOutput, strlen($apiOutput) - 3, 3);  
        $status = mapCode($outputCode);
        $sendingResult[$failCount] = array( "phoneNum" => $phoneNum, "name" => $name, "message" => $message, "result" => $status);
        if ($outputCode == "100"){ 
            $updateComponent = array("message" => $message, "timeSent" => $timeCreated, "status" => $status);
            SMSDB::updateSMSout($updateComponent);
            $index++;
            $attempt = 1;                        
        }else if ($attempt >= 3){            
            $updateComponent = array("message" => $message, "timeSent" => "", "status" => $status);
            SMSDB::updateSMSout($updateComponent);                        
            $index++;
            $attempt = 1;
        }	
        else{
            $attempt++;
        }
    }
    return $sendingResult;
}

function mapCode($code){
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
    }
}

?>
