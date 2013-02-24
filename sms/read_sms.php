<?php

header("Expires: 0");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");

//initialize session
session_start();

include('../class/SMSDB.php');

/*
  chdir('C:\xampp\htdocs\fscan\sms');
  $command = 'java -jar vigsyssmscom4.jar "2"';
  $output = shell_exec($command);
  echo $output;
 */

/*
  date_default_timezone_set('Asia/Singapore');
  $now = date("Y-m-d H:i:s");
  $oneDayEarlier = date_sub(date_create(date("Y-m-d H:i:s")), date_interval_create_from_date_string('1 day'))->format("Y-m-d H:i:s");
  print ("select * from fs_msgs where date >= '$oneDayEarlier' and date <= '$now';");
 */
/*
  chdir('C:\xampp\htdocs\fscan\sms');
  $command = 'java -jar vigsyssmscom4.jar "2"';
  $str_output = shell_exec($command);
  echo $str_output;
 */
/*
  $pos =  strpos($str_output,'VigSysSms v1.0-100:')+strlen('VigSysSms v1.0-100:');
  if($pos >20) $str_output = substr($str_output,$pos);
  echo $str_output;
 */

function readSMS($scheduleDate) {
    //guyu's part
    //for $msgSent, it each element is an associative array, with "smsId" "phoneNum" and "timeSent" (in 'y-m-d H:i:s' format)
    //give me only those messages that are sent (identified by its status ===> "OK") for the day (messages that are sent within 24 hrs from now)    
    set_time_limit(1200);    
    $msgSent = SMSDB::getSMSsent($scheduleDate);    
    $responseCount = 0;
    
    //date_default_timezone_set('Asia/Singapore');
    //$now = date("Y-m-d H:i:s");
    //$oneDayEarlier = date_sub(date_create(date("Y-m-d H:i:s")), date_interval_create_from_date_string('1 day'))->format("Y-m-d H:i:s");
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
            list($smsId, $message) = explode("-", $parts[$i + 3]); 
            $message = trim($message, " '");
            if (examineMsg($smsId, $phoneNum, $msgSent) != -1){
                $replied[$responseCount] = array("smsId" => $smsId, "timeReceived" => $timeReceived, "response" => $message);
                $responseCount++;
            }
        }
    }

    //Guyu's part, give me all messages received later than the given time
    //three elements, "phoneNum", "timeReceived", "message"
    $ifinsMsg = SMSDB::getIfinsSMSin($scheduleDate);

    for ($f = 0; $f < sizeof(ifinsMsg); $f++) {
        $phoneNum = $ifinsMsg[$f]["phoneNum"];
        $timeReceived = $ifinsMsg[$f]["timeReceived"];
        list($smsId, $message) = explode("-", $ifinsMsg[$f]["message"]);
        $message = trim($message, " '");
        if (examineMsg($smsId, $phoneNum, $msgSent) != -1){
                $replied[$responseCount] = array("smsId" => $smsId, "timeReceived" => $timeReceived, "response" => $message);
                $responseCount++;
        }
    }
    
    //Guyu's part, give you a smsId, check this piece of sms replied
    //and store their response
    SMSDB::markReplied($replied);   
}

function examineMsg($smsId, $phoneNum,$msgSent) {    
    return searchMsgSent($smsId, $phoneNum, $msgSent, 0, sizeof($msgSent) - 1);         
}

function searchMsgSent($smsId, $phoneNum, $msgSent, $start, $end) {
    if ($start > $end) {
        return -1;
    } else {
        $mid = ($start + $end) / 2;
        $compare = strcmp($msgSent[$mid]["smsId"], $smsId);
        if ($compare < 0) {
            searchMsgSent($smsId, $phoneNum, $msgSent, $mid + 1, $end);
        } else if ($compare > 0) {
            searchMsgSent($smsId, $phoneNum, $msgSent, $start, $mid - 1);
        } else {
            $interval = 0;
            while($mid < sizeof($msgSent) - $interval && $mid >= $interval ){
                if($smsSent[$mid + $interval]["phoneNum"] == $phoneNum){
                    return $mid + $interval;
                }else if($smsSent[$mid - $interval]["phoneNum"] == $phoneNum){
                    return $mid - $interval;
                }else{
                    $interval++;
                }                
            }
            return -1;
        }
    }
}

?>
