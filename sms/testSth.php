<?php
echo checkResponseRelevance("2013-02-25 18:59:59", "2013-02-25");

function checkResponseRelevance($timeReplied, $scheduleDate){
    $timeRepliedObj = date_create($timeReplied);    
    $scheduleDateObj = date_create($scheduleDate." 00:00:00"); 
    $timeDiff = date_diff($timeRepliedObj, $scheduleDateObj)->format("%R%h");
    $sign = substr($timeDiff, 0, 1);
    $hourDiff = substr($timeDiff, 1);
    if($sign == "-" && $hourDiff >= 18){
        print("false");
        return false;
    }else{
        print("true");
        return true;
    }
}

?>
