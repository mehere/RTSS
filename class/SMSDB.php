<?php
spl_autoload_register(function($class){
    require_once "$class.php";
});


class SMSDB
{
    public static function getMaxID()
    {
        $db_con = Constant::connect_to_db("ntu");
        if(empty($db_con))
        {
            throw new DBException('Fail to query max ID', __FILE__, __LINE__);
        }

        $sql_max = "select max(sms_id) as max from cm_sms_record;";
        $max_result = Constant::sql_execute($db_con, $sql_max);
        if(empty($max_result))
        {
            throw new DBException('Fail to query max ID', __FILE__, __LINE__);
        }
        if(is_null($max_result[0]['max']))
        {
            return 0;
        }

        return $max_result[0]['max'] - 0;
    }

    public static function storeSMSout($msg, $date)
    {
        $db_con = Constant::connect_to_db("ntu");
        if(empty($db_con))
        {
            throw new DBException('Fail to insert sms sent', __FILE__, __LINE__);
        }

        $sql_insert = "insert into cm_sms_record(phone_num, time_created, accname, is_replied, schedule_date, type) values ";

        $phone = mysql_real_escape_string(trim($msg['phoneNum'], " \t\n\r\0\x0B\""));
        $time_created = mysql_real_escape_string(trim($msg['timeCreated']));
        $accname = mysql_real_escape_string(trim($msg['accName']));

        $type = mysql_real_escape_string(trim($msg['type']));

        $sql_insert .= "('".$phone."','".$time_created."','".$accname."',false, '$date', '$type');";


        $insert_result = Constant::sql_execute($db_con, $sql_insert);
        if(empty($insert_result))
        {
            error_log(__CLASS__." ".__LINE__);
            error_log(var_export($date, true));
            error_log(mysql_error());
            throw new DBException('Fail to insert out messages', __FILE__, __LINE__);
        }

        return mysql_insert_id($db_con);
    }

    public static function updateSMSout($msg)
    {
        $db_con = Constant::connect_to_db("ntu");
        if(empty($db_con))
        {
            throw new DBException('Fail to insert sms sent', __FILE__, __LINE__);
        }

        $status = mysql_real_escape_string(trim($msg['status']));
        $message = mysql_real_escape_string(trim($msg['message']));
        $time_sent = mysql_real_escape_string(trim($msg['timeSent']));
        $smsId = mysql_real_escape_string($msg['smsId']);

        $sql_update = "update cm_sms_record set status = '".$status."', time_sent = '".$time_sent."', message = '".$message."' where sms_id = ".$smsId.";";

        $update_result = Constant::sql_execute($db_con, $sql_update);
        if(empty($update_result))
        {
            throw new DBException('Fail to update out messages', __FILE__, __LINE__);
        }
    }

    public static function storeSMSout_v2($msgs)
    {
        $db_con = Constant::connect_to_db("ntu");
        if(empty($db_con))
        {
            throw new DBException('Fail to insert sms sent', __FILE__, __LINE__);
        }

        if(count($msgs) === 0)
        {
            return;
        }
        
        $sql_insert = "insert into cm_sms_record(sms_id, phone_num, message, time_created, time_sent, status, accname, is_replied) values ";
        foreach($msgs as $msg)
        {
            $sms_id = mysql_real_escape_string($msg['smsId']);
            $phone = mysql_real_escape_string(trim($msg['phoneNum']));
            $message = mysql_real_escape_string(trim($msg['message']));
            $time_created = mysql_real_escape_string(trim($msg['timeCreated']));
            $time_sent = mysql_real_escape_string(trim($msg['timeSent']));
            $status = mysql_real_escape_string(trim($msg['status']));
            $accname = mysql_real_escape_string(trim($msg['accName']));

            $sql_insert .= "(".$sms_id.",".$phone.",".$message.",".$time_created.",".$time_sent.",".$status.",".$accname.",false),";
        }

        $sql_insert = substr($sql_insert, 0, -1).';';

        $insert_result = Constant::sql_execute($db_con, $sql_insert);
        if(empty($insert_result))
        {
            throw new DBException('Fail to insert out messages', __FILE__, __LINE__);
        }
    }

    /**
     *
     * @param string $scheduleDate yyyy-mm-dd
     */
    public static function getSMSsent($schedule_date)
    {
        $db_con = Constant::connect_to_db("ntu");
        if(empty($db_con))
        {
            throw new DBException('Fail to query sms sent', __FILE__, __LINE__);
        }

        //$sql_sms = "select sms_id as smsId, phone_num as phoneNum, message, DATE_FORMAT(time_created, '%Y/%m/%d %M:%i') as timeCreated, DATE_FORMAT(time_sent, '%Y/%m/%d %M:%i') as timeSent, status, accname as accName, is_replied as replied, DATE_FORMAT(time_replied, '%Y/%m/%d %M:%i') as timeReplied schedule_date as scheduleDate from cm_sms_record where scheduleDate = DATE(".$schedule_date.") and status = 'OK' order by smsId;";
        $sql_sms = "select sms_id as smsId, phone_num as phoneNum from cm_sms_record where schedule_date = DATE('".$schedule_date."') and status = 'OK' order by sms_id;";
        $sms_result = Constant::sql_execute($db_con, $sql_sms);
        if(is_null($sms_result))
        {
            throw new DBException("Fail to query sms sent", __FILE__, __LINE__);
        }

        return $sms_result;
    }

    /**
     *
     * @param string $scheduleDate yyyy-mm-dd
     */
    public static function getIfinsSMSin($schedule_date)
    {
        $db_con = Constant::connect_to_db("ntu");
        if(empty($db_con))
        {
            throw new DBException('Fail to query sms sent', __FILE__, __LINE__);
        }

        $sql_sms = "select phone_num, sms_id from cm_sms_record where schedule_date = DATE('".$schedule_date."') and status = 'OK';";

        $sms_result = Constant::sql_execute($db_con, $sql_sms);
        if(is_null($sms_result))
        {
            throw new DBException("Fail to query sms sent", __FILE__, __LINE__);
        }

        $sms_id_set = array();
        $sms_phone_set = array();

        foreach($sms_result as $row)
        {
            if(!in_array($row['phone_num'], $sms_phone_set))
            {
                $sms_phone_set[] = $row['phone_num'];
            }
            if(!in_array($row['sms_id'], $sms_id_set))
            {
                $sms_id_set[] = $row['sms_id'];
            }
        }

        if(count($sms_phone_set) > 0)
        {
            $sql_set = "(";
            foreach($sms_phone_set as $a_phone)
            {
                $sql_set .= "'".$a_phone."',";
            }
            $sql_set = substr($sql_set, 0, -1).");";

            //query reply
            $db_con_ifins = Constant::connect_to_db("ifins_real");
            if(empty($db_con_ifins))
            {
                throw new DBException('Fail to query sms reply', __FILE__, __LINE__);
            }

            $sql_reply = "select *, DATE_FORMAT(date, '%Y/%m/%d %H:%i') as time_received from fs_msgs where num in ".$sql_set;
            $reply_result = Constant::sql_execute($db_con_ifins, $sql_reply);
            if(is_null($reply_result))
            {
                throw new DBException("Fail to query sms reply ".$sql_reply, __FILE__, __LINE__, 2);
            }
        }

        else
        {
            return array();
            //throw new DBException($sql_set, __FILE__, __LINE__);
        }

        $result = array(); 

        foreach($reply_result as $a_reply)
        {
            $reply_msg = $a_reply['msg'];

            //reply format : 1232,YES, or 43,NO, or 34, in this case, assume the teacher accept the arrangement
            $break_reply = explode("-", $reply_msg);
            if(count($break_reply) === 0)
            {
                continue;
            }
            if(count($break_reply) === 1)
            {
                $sms_id = trim($break_reply[0]);
                $content = "yes";
            }
            else
            {
                $sms_id = trim($break_reply[0]);
                $content = trim($break_reply[1]);
            }
            
            if(!in_array($sms_id, $sms_id_set))
            {
                continue;
            }

            $a_sms = Array(
                "phoneNum" => $a_reply['num'],
                "timeReceived" => $a_reply['time_received'],
                "message" => $a_reply['msg']
            );

            $result[] = $a_sms;
        }

        return $result;
    }

    public static function markReplied($replied)
    {
        $db_con = Constant::connect_to_db("ntu");
        if(empty($db_con))
        {
            throw new DBException('Fail to mark reply', __FILE__, __LINE__);
        }

        foreach($replied as $id => $reply)
        {
            $sql_update = "update cm_sms_record set is_replied = true, time_replied = '".mysql_real_escape_string(trim($reply['timeReceived']))."', response = '".mysql_real_escape_string(trim($reply['response']))."' where sms_id = ".mysql_real_escape_string(trim($id)).";";
            Constant::sql_execute($db_con, $sql_update);
        }
    }

    /**
     *
     * @param type $date
     * @param type $order
     * @param type $direction
     * @param string $type "R": relief msg; "C": cancel msg
     * @return type
     * @throws DBException
     */
    public static function allSMSStatus($date, $order = 'fullname', $direction = SORT_ASC, $type = 'R')
    {
        SMS::readSMS($date);

        $normal_dict = Teacher::getAllTeachers();
        $temp_dict = Teacher::getTempTeacher("");

        $db_con = Constant::connect_to_db("ntu");
        if(empty($db_con))
        {
            throw new DBException('Fail to query SMS reply status', __FILE__, __LINE__);
        }

        $direction_db = Array(
            SORT_ASC => 'ASC',
            SORT_DESC => 'DESC'
        );
        $order_db = Array(
            'sentTime' => 'time_sent',
            'phone' => 'phone_num',
            'status' => 'status',
            'repliedTime' => 'time_replied',
            'repliedMsg' => 'response'
        );

        $type_trimed = mysql_real_escape_string(trim($type));
        $sql_query_sms = "select *, DATE_FORMAT(time_sent, '%H:%i') as sent_time,DATE_FORMAT(time_replied, '%H:%i') as replied_time  from cm_sms_record where type = '$type_trimed' and schedule_date = DATE('$date')";

        if(array_key_exists($order, $order_db))
        {
            $sql_query_sms .= " order by ".$order_db[$order]." ".$direction_db[$direction].";";
        }
        else
        {
            $sql_query_sms .= ";";
        }

        $query_result = Constant::sql_execute($db_con, $sql_query_sms);
        if(is_null($query_result))
        {
            throw new DBException('Fail to query SMS reply status '.$sql_query_sms, __FILE__, __LINE__);
        }

        $result = Array();
        foreach($query_result as $row)
        {
            if(array_key_exists($row['accname'], $temp_dict))
            {
                $name = $temp_dict[$row['accname']]['fullname'];
            }
            else if(array_key_exists($row['accname'], $normal_dict))
            {
                $name = $normal_dict[$row['accname']]['name'];
            }
            else
            {
                $name = "";
            }

            $a_sms = Array(
                "sentTime" => $row['sent_time'],
                "fullname" => $name,
                "phone" => $row['phone_num'],
                "status" => $row['status'],
                "repliedTime" => $row['replied_time'],
                "repliedMsg" => $row['response']
            );

            $result[] = $a_sms;
        }

        if(strcmp($order, "fullname") === 0)
        {
             uasort($result, 'SMSDB::compareName');
        }
        
        return $result;
    }
    
    private static function compareName($a, $b)
    {
        return strcasecmp($a['fullname'], $b['fullname']);
    }
}

?>
