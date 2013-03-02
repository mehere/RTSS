<?php

header("Expires: 0");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");

include('../SMSDB.php');

function readSMS($scheduleDate) {
    set_time_limit(1200);
    $msgSent = SMSDB::getSMSsent($scheduleDate);    

    chdir('C:\xampp\htdocs\fscan\sms');
    $command = 'java -jar vigsyssmscom4.jar "2"';
    $output = shell_exec($command);
    $startPos = strpos($output, 'VigSysSms v1.0-100:') + strlen('VigSysSms v1.0-100:');
    if ($startPos > 20) {
        $msgString = substr($output, $startPos);
        $parts = explode("###", $msgString);
        for ($i = 0; $i < sizeof($parts) - 3; $i += 4) {
            $phoneNum = $parts[$i + 1];
            $timeReceived = $parts[$i + 2];
            $response = $parts[$i + 3];
            $response = trim($response, " '");
            list($smsId, $message) = explode("-", $response);                                                
            list($time, $date) = explode(" ", $timeReceived);
            list($day, $month, $year) = explode("-", $date);
            $year = "20".$year;
            $date = "$year-$month-$day";
            $timeReceived = "$date $time"; 
            if(checkResponseRelevance($timeReceived, $scheduleDate)){
                if (examineMsg($smsId, $phoneNum, $msgSent) != -1) {
                    $replied[] = array("smsId" => $smsId, "timeReceived" => $timeReceived, "response" => $message);                
                }
            }            
        }
    }

    $ifinsMsg = SMSDB::getIfinsSMSin($scheduleDate);
    
    for ($f = 0; $f < sizeof($ifinsMsg); $f++) {
        $phoneNum = $ifinsMsg[$f]["phoneNum"];
        $timeReceived = $ifinsMsg[$f]["timeReceived"];
        list($smsId, $message) = explode("-", $ifinsMsg[$f]["message"]);        
        $message = trim($message, " '");
        if (checkResponseRelevance($timeReceived, $scheduleDate)) {
            if (examineMsg($smsId, $phoneNum, $msgSent) != -1) {
                $replied[] = array("smsId" => $smsId, "timeReceived" => $timeReceived, "response" => $message);
            }
        }
    }        

    if(sizeof($replied) > 0){
        SMSDB::markReplied($replied);   
    }    
}

function examineMsg($smsId, $phoneNum, $msgSent) {        
    return searchMsgSent($smsId, $phoneNum, $msgSent, 0, sizeof($msgSent) - 1);
}

function searchMsgSent($smsId, $phoneNum, $msgSent, $start, $end) {
    if ($start > $end) {
        return -1;
    } else {
        $mid = intval(($start + $end) / 2);
        $compare = strcmp($msgSent[$mid]["smsId"], $smsId);
        if ($compare < 0) {
            return searchMsgSent($smsId, $phoneNum, $msgSent, $mid + 1, $end);
        } else if ($compare > 0) {
            return searchMsgSent($smsId, $phoneNum, $msgSent, $start, $mid - 1);
        } else {
            $interval = 0;
            while ($mid < sizeof($msgSent) - $interval || $mid >= $interval) {  
                $leftBoundOut = false;
                $rightBoundOut = false;
                if($mid < sizeof($msgSent) - $interval){
                    if($smsId == $msgSent[$mid + $interval]["smsId"] && $msgSent[$mid + $interval]["phoneNum"] == $phoneNum){                        
                        return $mid + $interval;                                                
                    } 
                    if($smsId != $msgSent[$mid + $interval]["smsId"]){
                        $rightBoundOut = true;
                    }                        
                }                
                if ($mid >= $interval) {
                    if ($smsId == $msgSent[$mid - $interval]["smsId"] && $msgSent[$mid - $interval]["phoneNum"] == $phoneNum) {                                                
                        return $mid - $interval;
                    }                    
                    if($smsId != $msgSent[$mid - $interval]["smsId"]){
                        $leftBoundOut = true;
                    }                        
                }                
                if ($rightBoundOut && $leftBoundOut){
                    return -1;
                }
                $interval++;                
            }            
            return -1;
        }
    }
}

function checkResponseRelevance($timeReplied, $scheduleDate){
    $timeRepliedObj = date_create($timeReplied);    
    $scheduleDateObj = date_create($scheduleDate." 00:00:00"); 
    $timeDiff = date_diff($timeRepliedObj, $scheduleDateObj)->format("%R %d %h");
    list($sign, $dayDiff, $hourDiff) = explode(" ", $timeDiff);    
    if($sign == "-" && ($hourDiff >= 18 || $dayDiff >= 1)){        
        return false;
    }else{        
        return true;
    }
}

?>
