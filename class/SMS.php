<?php
require_once 'SMSDB.php';

class SMS {

    public static function sendSMS($receiverList, $scheduleDate) {
        date_default_timezone_set('Asia/Singapore');
        set_time_limit(1200);
        $index = 0;
        $attempt = 1;
        
        //test
        $print_command = array();
        //end of test
        
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
                //chdir('C:\xampp\htdocs\fscan\sms');
                $command = 'java -jar vigsyssmscom4.jar "1" "' . $phoneNum . '" "' . $message . '"';
                
                //test
                $print_command[] = $command;
                $outputCode = 100;
                //end of test
                
                //$apiOutput = shell_exec($command . "\n");
                //$outputCode = substr($apiOutput, strlen($apiOutput) - 3, 3);
            } else {
                $outputCode = 104;
                $attemp = 3;
            }
            $status = SMS::mapCode($outputCode);
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
        
        //test
//        $file = fopen('sms_test.txt', 'w');
//        foreach ($print_command as $gem)
//        {
//            fwrite($file, $gem.'\r\n');
//        }
//        fclose($file);
        //end of test
        
        return $sendingResult;
    }

    public static function mapCode($code) {
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

    public static function readSMS($scheduleDate) {
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
                $year = "20" . $year;
                $date = "$year-$month-$day";
                $timeReceived = "$date $time";
                if (SMS::checkResponseRelevance($timeReceived, $scheduleDate)) {
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
            if (SMS::checkResponseRelevance($timeReceived, $scheduleDate)) {
                if (examineMsg($smsId, $phoneNum, $msgSent) != -1) {
                    $replied[] = array("smsId" => $smsId, "timeReceived" => $timeReceived, "response" => $message);
                }
            }
        }

        if (sizeof($replied) > 0) {
            SMSDB::markReplied($replied);
        }
    }

    public static function examineMsg($smsId, $phoneNum, $msgSent) {
        return SMS::searchMsgSent($smsId, $phoneNum, $msgSent, 0, sizeof($msgSent) - 1);
    }

    public static function searchMsgSent($smsId, $phoneNum, $msgSent, $start, $end) {
        if ($start > $end) {
            return -1;
        } else {
            $mid = intval(($start + $end) / 2);
            $compare = strcmp($msgSent[$mid]["smsId"], $smsId);
            if ($compare < 0) {
                return SMS::searchMsgSent($smsId, $phoneNum, $msgSent, $mid + 1, $end);
            } else if ($compare > 0) {
                return SMS::searchMsgSent($smsId, $phoneNum, $msgSent, $start, $mid - 1);
            } else {
                $interval = 0;
                while ($mid < sizeof($msgSent) - $interval || $mid >= $interval) {
                    $leftBoundOut = false;
                    $rightBoundOut = false;
                    if ($mid < sizeof($msgSent) - $interval) {
                        if ($smsId == $msgSent[$mid + $interval]["smsId"] && $msgSent[$mid + $interval]["phoneNum"] == $phoneNum) {
                            return $mid + $interval;
                        }
                        if ($smsId != $msgSent[$mid + $interval]["smsId"]) {
                            $rightBoundOut = true;
                        }
                    }
                    if ($mid >= $interval) {
                        if ($smsId == $msgSent[$mid - $interval]["smsId"] && $msgSent[$mid - $interval]["phoneNum"] == $phoneNum) {
                            return $mid - $interval;
                        }
                        if ($smsId != $msgSent[$mid - $interval]["smsId"]) {
                            $leftBoundOut = true;
                        }
                    }
                    if ($rightBoundOut && $leftBoundOut) {
                        return -1;
                    }
                    $interval++;
                }
                return -1;
            }
        }
    }

    public static function checkResponseRelevance($timeReplied, $scheduleDate) {
        $timeRepliedObj = date_create($timeReplied);
        $scheduleDateObj = date_create($scheduleDate . " 00:00:00");
        $timeDiff = date_diff($timeRepliedObj, $scheduleDateObj)->format("%R %d %h");
        list($sign, $dayDiff, $hourDiff) = explode(" ", $timeDiff);
        if ($sign == "-" && ($hourDiff >= 18 || $dayDiff >= 1)) {
            return false;
        } else {
            return true;
        }
    }

}

?>
