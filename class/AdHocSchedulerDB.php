<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
spl_autoload_register(function($class){
    require_once "$class.php";
});

class AdHocSchedulerDB
{
    public static function getReliefPlan($scheduleDate)
    {
        //check date validity
        /*
        $sem_id = TimetableDB::checkTimetableExistence(0, array('date'=>$scheduleDate));
        if($sem_id === -1)
        {
            throw new DBException('No lesson information on '.$scheduleDate, __FILE__, __LINE__, 1);
        }
         *
         */

        //db connection
        $db_con = Constant::connect_to_db("ntu");
        if(empty($db_con))
        {
            throw new DBException('Fail to connect to database'.$scheduleDate, __FILE__, __LINE__);
        }

        //query
        $sql_get_relief = "select * from rs_relief_info where schedule_date = DATE('$scheduleDate');";
        $relief_result = Constant::sql_execute($db_con, $sql_get_relief);
        if(is_null($relief_result))
        {
            throw new DBException('Fail to query relief'.$scheduleDate, __FILE__, __LINE__, 2);
        }

        //return data structure cnotruction
        $result = array();

        foreach($relief_result as $row)
        {
            $leave_teacher = $row['leave_teacher'];
            $relief_teacher = $row['relief_teacher'];
            $start_time = $row['start_time_index'];
            $end_time = $row['end_time_index'];
            $lesson_id = $row['lesson_id'];

            $a_relief = new ReliefLesson($leave_teacher, $lesson_id, $start_time);
            $a_relief->teacherRelief = $relief_teacher;
            $a_relief->endTimeSlot = $end_time;

            $result[] = $a_relief;
        }

        return $result;
    }

    public static function getSkippingPlan($scheduleDate)
    {
        //db connection
        $db_con = Constant::connect_to_db('ntu');
        if(empty($db_con))
        {
            throw new DBException('Fail to connect to database', __FILE__, __LINE__);
        }

        //query
        $sql_skip = "select * from rs_aed_skip_info where schedule_date = DATE('$scheduleDate');";
        $skip_result = Constant::sql_execute($db_con, $sql_skip);
        if(is_null($skip_result))
        {
            throw new DBException('Fail to query skip info on '.$scheduleDate, __FILE__, __LINE__, 2);
        }

        //return result
        $result = array();
        
        foreach($skip_result as $row)
        {
            $accname = $row['accname'];
            $start_time = $row['start_time_index'];
            $end_time = $row['end_time_index'];
            $lesson_id = $row['lesson_id'];

            $a_skip = new ReliefLesson($accname, $lesson_id, $start_time);
            $a_skip->endTimeSlot = $end_time;
            $a_skip->teacherRelief = $accname;

            $result[] = $a_skip;
        }

        return $result;
    }

    public static function getBlockingPlan($scheduleDate)
    {
        //connect
        $db_con = Constant::connect_to_db('ntu');
        if(empty($db_con))
        {
            throw new DBException('Fail to connect to database', __FILE__, __LINE__);
        }

        //query
        $sql_block = "select * from (temp_ah_cancelled_relief left join rs_relief_info on temp_ah_cancelled_relief.relief_id = rs_relief_info.relief_id) where temp_ah_cancelled_relief.schedule_date = DATE('$scheduleDate');";
        $block_result = Constant::sql_execute($db_con, $sql_block);
        if(is_null($block_result))
        {
            throw new DBException('Fail to query block info on '.$scheduleDate, __FILE__, __LINE__, 2);
        }

        //result
        $result = array();

        foreach($block_result as $row)
        {
            $start_time_index = $row['block_start_index'];
            $end_time_index = $row['block_end_index'];
            $accname = $row['relief_teacher'];

            $day_time = new DayTime(0, $start_time_index);

            $a_block = new Lesson($day_time, 'Unavailable', '');
            $a_block->endTimeSlot = $end_time_index;
            $a_block->teachers[] = $accname;

            $result[] = $a_block;
        }

        return $result;
    }
    
    /**
     * 
     * @param int $reliefID
     * @param int $startBlockingTime 1-based
     * @param int $endBlockingTime 1-based
     * @return type
     * @throws DBException
     */
    public static function cancelRelief($reliefID, $startBlockingTime, $endBlockingTime)
    {
        $db_con = Constant::connect_to_db('ntu');
        if(empty($db_con))
        {
            throw new DBException('Fail to connect to database', __FILE__, __LINE__);
        }
        
        //retrieve relief
        $sql_relief = "select * from rs_relief_info where relief_id = $reliefID;";
        $relief_result = Constant::sql_execute($db_con, $sql_relief);
        if(is_null($relief_result))
        {
            throw new DBException('Fail to retrieve relief info', __FILE__, __LINE__, 2);
        }
        
        if(empty($relief_result))
        {
            return;
        }
        
        $relief = $relief_result[0];
        
        $schedule_date = $relief['schedule_date'];
        $relief_teacher = $relief['relief_teacher'];
        $relief_start = $relief['start_time_index'];
        $relief_end = $relief['end_time_index'];
        $lesson_id = $relief['lesson_id'];
        $leave_teacher = $relief['leave_teacher'];
        
        //store cancel
        $sql_insert_cancel = "insert into temp_ah_cancelled_relief values ($reliefID, '$schedule_date', $startBlockingTime, $endBlockingTime);";
        $cancel_result = Constant::sql_execute($db_con, $sql_insert_cancel);
        if(is_null($cancel_result))
        {
            throw new DBException('Fail to insert cancel relief', __FILE__, __LINE__, 2);
        }
        
        //search all relief
        $sql_all_relief = "select start_time_index, end_time_index from rs_relief_info where schedule_date = DATE('$schedule_date') and relief_teacher = '$relief_teacher';";
        $all_relief_result = Constant::sql_execute($db_con, $sql_all_relief);
        if(is_null($all_relief_result))
        {
            throw new DBException('Fail to query all relief duties', __FILE__, __LINE__, 2);
        }
        
        $have_class_index = array();  //array of start time index of releif duties
        
        foreach($all_relief_result as $row)
        {
            $start_time = $row['start_time_index'];
            $end_time = $row['end_time_index'];
            
            for($i = $start_time; $i < $end_time; $i++)
            {
                $have_class_index[] = $i;
            }
        }
        
        //search all rs_aed_skip
        $sql_all_skip = "select * from rs_aed_skip_info where schedule_date = DATE('$schedule_date') and accname = '$relief_teacher';";
        $all_skip_result = Constant::sql_execute($db_con, $sql_all_skip);
        if(is_null($all_skip_result))
        {
            throw new DBException('Fail to query all skipped lessons', __FILE__, __LINE__, 2);
        }
        
        $skip_array = array();
        foreach($all_skip_result as $row)
        {
            $skip_array[$row['start_time_index']] = $row['skip_id'];
        }
        
        //find skip ids to be recovered
        $diff = $relief_end - $relief_start;
        $recover_list = array();    //skip ids to be deleted
        
        for($i = $relief_start; $i < $relief_end; $i++)
        {
            if(!empty($skip_array[$i]))
            {
                $recover_list[] = $skip_array[$i];
            }
        }
        
        foreach ($skip_array as $start=>$id)
        {
            if(count($recover_list) >= $diff)
            {
                break;
            }
            
            if(!in_array($start, $have_class_index))
            {
                $recover_list[] = $id;
            }
        }
        
        //delete relief
        $sql_delete_relief = "delete from rs_relief_info where relief_id = '$reliefID';";
        $delete_relief_result = Constant::sql_execute($db_con, $sql_delete_relief);
        if(is_null($delete_relief_result))
        {
            throw new DBException("Fail to cancel the relief", __FILE__, __LINE__, 2);
        }
        
        //delete skip - error : restore relief
        if(count($recover_list) > 0)
        {
            $sql_delete_skip = "delete from rs_aed_skip_info where skip_id in (".implode(",", $recover_list).");";
            $delete_skip_result = Constant::sql_execute($db_con, $sql_delete_skip);
            if(is_null($delete_skip_result))
            {
                $sql_recover_relief = "insert into rs_relief_info(relief_id, lesson_id, schedule_date, start_time_index, end_time_index, leave_teacher, relief_teacher, num_of_slot) values ($reliefID, '$lesson_id', '$schedule_date', $relief_start, $relief_end, '$leave_teacher', '$relief_teacher', $diff);";
                $recover_relief_result = Constant::sql_execute($db_con, $sql_recover_relief);
                if(is_null($recover_relief_result))
                {
                    throw new DBException('Fatal !!! : Fail to cancel relief and fail to recover to previous state, resulting in data inconsistency. Reschedule for all teachers are highly recommended.', __FILE__, __LINE__, 2);
                }

                throw new DBException('Fail to cancel teh relief', __FILE__, __LINE__, 2);
            }
        }
    }
    
    public static function adHocApprove($schedule_index, $date)
    {
        $teacher_list = Teacher::getTeacherContact();
        
        $db_con = Constant::connect_to_db("ntu");
        if (empty($db_con))
        {
            throw new DBException('Fail to approve the schedule', __FILE__, __LINE__);
        }
        
        $final_result = array(
            "cancelNotified" => array(),
            "reliefNotified" => array()
        );
        
        //0. delete cancelled relief and associated skip
        $sql_delete_cancelled_relief = "delete from rs_relief_info where relief_id in (select distinct relief_id_ref from rs_aed_skip_info);";
        $delete_cancelled_relief = Constant::sql_execute($db_con, $sql_delete_cancelled_relief);
        if(is_null($delete_cancelled_relief))
        {
            throw new DBException('Fail to approve the schedule', __FILE__, __LINE__, 2);
        }
        
        //1. move from temp to relief_info and delete temp
        //get leaves
        $sql_select_leave = "select * from rs_leave_info where DATE('$date') between DATE(start_time) and DATE(end_time);";
        $select_leave = Constant::sql_execute($db_con, $sql_select_leave);
        if(is_null($select_leave))
        {
            throw new DBException('Fail to clear exist relief record', __FILE__, __LINE__, 2);
        }
        
        $leave_dict = array();
        foreach($select_leave as $row)
        {
            $accname = $row['teacher_id'];
            
            if(empty($leave_dict[$accname]))
            {
                $leave_dict[$accname] = array();
            }
            
            $leave_id = $row['leave_id'];
            
            $time_zone = new DateTimeZone('Asia/Singapore');
            
            $start_time_str = $row['start_time'];
            $start_time_obj = new DateTime($start_time_str);
            $start_time_obj->setTimezone($time_zone);
            $start_time_stamp = $start_time_obj->getTimestamp();
            
            $end_time_str = $row['end_time'];
            $end_time_obj = new DateTime($end_time_str);
            $end_time_obj->setTimezone($time_zone);
            $end_time_stamp = $end_time_obj->getTimestamp();
            
            $leave_dict[$accname][] = array($start_time_stamp, $end_time_stamp, $leave_id);
        }
        
        //copy selected one
        $sql_select_temp = "select lesson_id, schedule_date, start_time_index, end_time_index, leave_teacher, relief_teacher, num_of_slot, leave_id_ref from temp_each_alternative where schedule_id = $schedule_index";
        $select_temp = Constant::sql_execute($db_con, $sql_select_temp);
        if (is_null($select_temp))
        {
            throw new DBException('Fail to approve the schedule', __FILE__, __LINE__, 2);
        }
        
        if(count($select_temp) > 0)
        {
            $sql_insert_select = "insert into rs_relief_info (lesson_id, schedule_date, start_time_index, end_time_index, leave_teacher, relief_teacher, num_of_slot, leave_id_ref) values ";
            foreach($select_temp as $row)
            {
                $lesson_id = $row['lesson_id'];
                $schedule_date = $row['schedule_date'];
                $start_time_relief = $row['start_time_index'];
                $end_time_relief = $row['end_time_index'];
                $leave_teacher = $row['leave_teacher'];
                $relief_teacher = $row['relief_teacher'];
                $num_of_slot = $row['num_of_slot'];

                $start_time_value = SchoolTime::getTimeValue($start_time_relief);

                $start_time_relief_obj = new DateTime($schedule_date." ".$start_time_value);
                $start_time_relief_obj->setTimezone($time_zone);
                $start_time_relief_stamp = $start_time_relief_obj->getTimestamp();

                $leave_id_ref = "NULL";
                foreach($leave_dict[$leave_teacher] as $row)
                {
                    if($start_time_relief_stamp >= $row[0] && $start_time_relief_stamp <= $row[1])
                    {
                        $leave_id_ref = $row[2];
                    }
                }

                $sql_insert_select .= "('$lesson_id', '$schedule_date', $start_time_relief, $end_time_relief, '$leave_teacher', '$relief_teacher', $num_of_slot, $leave_id_ref),";
            }
            $sql_insert_select = substr($sql_insert_select, 0, -1).';';

            $insert_result = Constant::sql_execute($db_con, $sql_insert_select);
            if (is_null($insert_result))
            {
                throw new DBException('Fail to approve the schedule', __FILE__, __LINE__, 2);
            }
        }

        //delete temp later as need to send sms and email

        //2. move and delete skip - match relief_id to skip first
        $sql_select_relief = "select * from rs_relief_info where schedule_date = DATE('$date')";
        $select_relief_result = Constant::sql_execute($db_con, $sql_select_relief);
        if(is_null($select_relief_result))
        {
            throw new DBException('Fail to clear temporary schedules', __FILE__, __LINE__, 2);
        }
        $relief_dict = array();
        foreach($select_relief_result as $row)
        {
            $accname = $row['relief_teacher'];
            if(empty($relief_dict[$accname]))
            {
                $relief_dict[$accname] = array();
            }
            
            $start_time = $row['start_time_index'];
            $end_time = $row['end_time_index'];
            $relief_id = $row['relief_id'];
            
            for($i = $start_time; $i < $end_time; $i++)
            {
                $relief_dict[$accname][$i] = $relief_id;
            }
        }
        
        $sql_select_temp_skip = "select * from temp_aed_skip_info where schedule_id = $schedule_index;";
        $select_temp_skip = Constant::sql_execute($db_con, $sql_select_temp_skip);
        if(is_null($select_temp_skip))
        {
            throw new DBException('Fail to clear exist skip record', __FILE__, __LINE__, 2);
        }
        
        if(count($select_temp_skip) > 0)
        {
            $sql_insert_skip = "insert into rs_aed_skip_info (lesson_id, schedule_date, start_time_index, end_time_index, accname, relief_id_ref) values ";
            foreach($select_temp_skip as $row)
            {
                $lesson_id = $row['lesson_id'];
                $start_time_skip = $row['start_time_index'];
                $end_time_skip = $start_time_skip + 1;
                $accname = $row['accname'];
                $relief_id_ref = empty($relief_dict[$accname][$start_time_skip])?'NULL':$relief_dict[$accname][$start_time_skip];

                $sql_insert_skip .= "('$lesson_id', '$date', $start_time_skip, $end_time_skip, '$accname', $relief_id_ref),";
            }
            $sql_insert_skip = substr($sql_insert_skip, 0, -1).';';
        
            $insert_skip_result = Constant::sql_execute($db_con, $sql_insert_skip);
            if (is_null($insert_skip_result))
            {
                throw new DBException('Fail to approve the schedule', __FILE__, __LINE__, 2);
            }
        }

        $sql_delete_skip = "delete from temp_aed_skip_info;";
        $delete_skip_result = Constant::sql_execute($db_con, $sql_delete_skip);
        if (is_null($delete_skip_result))
        {
            throw new DBException('Fail to clear temporary skip record', __FILE__, __LINE__, 2);
        }

        //3. construct sms cancel relief content
        $sql_cancelled = "select temp_ah_cancelled_relief.* from temp_ah_cancelled_relief where schedule_date = DATE('$date');";
        $cancelled_result = Constant::sql_execute($db_con, $sql_cancelled);
        if(is_null($cancelled_result))
        {
            throw new DBException("Fail to send cancel information", __FILE__, __LINE__, 2);
        }
        
        $cancel_sms_list = array();
        foreach($cancelled_result as $row)
        {
            $accname = $row['accname'];
            
            $block_start_index = $row['block_start_index'];
            $block_end_index = $row['block_end_index'];
            $block_start = SchoolTime::getTimeValue($block_start_index);
            $block_end = SchoolTime::getTimeValue($block_end_index);
            
            if(array_key_exists($accname, $cancel_sms_list))
            {
                $cancel_sms_list[$accname]['message'] .= " $block_start - $block_end ;";
            }
            else
            {
                if(array_key_exists($accname, $teacher_list))
                {
                    $phone = $teacher_list[$accname]['phone'];
                    $name = $teacher_list[$accname]['name'];
                }
                if (empty($phone))
                {
                    continue;
                }
                if (empty($name))
                {
                    $name = "Teacher";
                }
                
                $final_result['cancelNotified'][$accname]  = array(
                    'smsSent' => 0,
                    'emailSent' => 0,
                    'fullname' => $name
                );
                
                $message = "Dear Teacher, your relief duties during following time period(s) on $date has been cancelled. The skipped optional lessons will be recovered. For more details, please check iScheduler website or contact admin : ";
                $message .= " $block_start - $block_end; ";
                
                $one_cancel = array(
                    "phoneNum" => $phone,
                    "name" => $name,
                    "accName" => $accname,
                    "message" => $message,
                    "type" => 'C'
                );

                $cancel_sms_list[$accname] = $one_cancel;
            }
        }
        
        //3.1. delete cancel list
        $sql_delete_cancelled = "delete from temp_ah_cancelled_relief where schedule_date = DATE('$date');";
        $delete_cancelled = Constant::sql_execute($db_con, $sql_delete_cancelled);
        if(is_null($delete_cancelled))
        {
            throw new DBException('Fail to delete relief cancellation', __FILE__, __LINE__);
        }
        
        //4. send cancel sms
        $cancel_sms_reply = SMS::sendSMS($cancel_sms_list, $date);

        if (!is_null($cancel_sms_reply))
        {
            foreach ($cancel_sms_reply as $a_reply)
            {
                $accname = $a_reply['accname'];
                if (array_key_exists($accname, $final_result['cancelNotified']))
                {
                    if (strcmp($a_reply['status'], 'OK') === 0)
                    {
                        $final_result['cancelNotified'][$accname]['smsSent'] = 1;
                    }
                }
            }
        }
        
        //5. construct sms relief content
        $sql_selected = "select temp_each_alternative.lesson_id, temp_each_alternative.start_time_index, temp_each_alternative.end_time_index, relief_teacher, subj_code, venue, class_name from ((temp_each_alternative left join ct_lesson on temp_each_alternative.lesson_id = ct_lesson.lesson_id) left join ct_class_matching on ct_lesson.lesson_id = ct_class_matching.lesson_id) where DATE(temp_each_alternative.schedule_date) = DATE('".$date."') and temp_each_alternative.schedule_id = $schedule_index;";
        $selected_result = Constant::sql_execute($db_con, $sql_selected);
        if (is_null($selected_result))
        {
            throw new DBException('Fail to send sms', __FILE__, __LINE__);
        }

        //a list of relief teacher, with their relief duties
        //{accname => {unique_relief_key=>{...}, ...}, ...}
        $list = array(); // for construct msg content
        foreach ($selected_result as $row)
        {
            $accname = $row['relief_teacher'];

            if (!array_key_exists($accname, $list))
            {
                $list[$accname] = array();
            }

            $unique_relief_key = $row['lesson_id'] . $row['start_time_index'] . $row['end_time_index'];
            if (array_key_exists($unique_relief_key, $list[$accname]))
            {
                if (!empty($row['class_name']))
                {
                    $list[$accname][$unique_relief_key]['class'][] = $row['class_name'];
                }
            } else
            {
                $venue = empty($row['venue']) ? "" : $row['venue'];
                $subject = empty($row['subj_code']) ? "" : $row['subj_code'];

                $one_relief = Array(
                    "start_time" => $row['start_time_index'] - 0,
                    "end_time" => $row['end_time_index'] - 0,
                    "subject" => $subject,
                    "venue" => $venue,
                    "class" => Array()
                );

                if (!empty($row['class_name']))
                {
                    $one_relief['class'][] = $row['class_name'];
                }

                $list[$accname][$unique_relief_key] = $one_relief;
            }
        }

        $sms_input = Array();
        foreach ($list as $key => $one)
        {
            $accname = $key;

            if (!array_key_exists($accname, $teacher_list))
            {
                continue;
            }

            $phone = $teacher_list[$accname]['phone'];
            $name = $teacher_list[$accname]['name'];

            if (empty($name))
            {
                $name = "Teacher";
            }
            
            $final_result['reliefNotified'][$accname] = array(
                'fullname' => $name,
                'smsSent' => 0,
                'emailSent' => 0
            );

            if (empty($phone))
            {
                continue;
            }
            
            $message = "";

            $index = 1;
            foreach ($one as $a_relief)
            {
                $start_time = SchoolTime::getTimeValue($a_relief['start_time']);
                $end_time = SchoolTime::getTimeValue($a_relief['end_time']);

                $classes = implode(",", $a_relief['class']);
                $subject = $a_relief['subject'];
                $venue = empty($a_relief['venue']) ? "in classroom" : $a_relief['venue'];

                $message .= "|    $index : On $date $start_time-$end_time take relief for $classes subject-$subject venue-$venue  |";

                $index++;
            }

            $one_teacher = Array(
                "phoneNum" => $phone,
                "name" => $name,
                "accName" => $accname,
                "message" => $message,
                "type" => 'R'
            );

            $sms_input[] = $one_teacher;
        }

        //6. send relief sms
        $sms_reply = SMS::sendSMS($sms_input, $date);

        if (!is_null($sms_reply))
        {
            foreach ($sms_reply as $a_reply)
            {
                $accname = $a_reply['accname'];
                if (array_key_exists($accname, $final_result['reliefNotified']))
                {
                    if (strcmp($a_reply['status'], 'OK') === 0)
                    {
                        $final_result['reliefNotified'][$accname]['smsSent'] = 1;
                    }
                }
            }
        }

        //7. construct cancel email
        $from = array(
            "email" => Constant::email,
            "password" => Constant::email_password,
            "name" => Constant::email_name,
            "smtp" => Constant::email_smtp,
            "port" => Constant::email_port,
            "encryption" => Constant::email_encryption
        );

        $cancel_to = array();
        foreach($cancel_sms_list as $a_cancel)
        {
            $accname = $a_cancel['accName'];
            
            $a_cancel_email = array(
                "subject" => "Relief duty cancellation notification",
                "message" => $a_cancel['message'],
                'attachment' => "",
                'accname' => $accname
            );
            
            
            if(!array_key_exists($accname, $teacher_list))
            {
                continue;
            }
            
            $email = $teacher_list[$accname]['email'];
            $name = $teacher_list[$accname]['name'];
            
            if(empty($email))
            {
                continue;
            }
            if(empty($name))
            {
                $name = Teacher;
            }
            
            $a_cancel_email['name'] = $name;
            $a_cancel_email['email'] = $email;
            
            $cancel_to = $a_cancel_email;
        }
        
        //8. send cancel email
        /*
        $cancel_email_reply = Email::sendMail($from, $cancel_to);

        if (!is_null($cancel_email_reply))
        {
            foreach ($cancel_email_reply as $accname => $a_reply)
            {
                if ($a_reply === 1)
                {
                    $final_result['cancelNotified'][$accname]['emailSent'] = 1;
                }
            }
        }
         * 
         */
        //9. construct relief email
        $to = array();
        foreach ($list as $key => $one)
        {
            $accname = $key;

            if (!array_key_exists($accname, $teacher_list))
            {
                continue;
            }

            $name = $teacher_list[$accname]['name'];
            $email = $teacher_list[$accname]['email'];

            if (empty($email))
            {
                continue;
            }
            if (empty($name))
            {
                $name = 'Teacher';
            }

            $email_timetable_input = array();
            foreach ($one as $a_relief)
            {
                $start_time = $a_relief['start_time'] - 1;
                $end_time = $a_relief['end_time'] - 1;

                for($i = $start_time; $i < $end_time; $i++)
                {
                    $subject = $a_relief['subject'];
                    $venue = empty($a_relief['venue']) ? "in classroom" : $a_relief['venue'];
                    
                    $email_timetable_input[$i] = array(
                        "class" => $a_relief['class'],
                        "subject" => $subject,
                        "venue" => $venue
                    );
                }
            }

            $message = Email::formatEmail($name, $date, $email_timetable_input, Constant::email_name);

            $recepient = array(
                'accname' => $accname,
                'subject' => 'Relief timetable for today',
                'email' => $email,
                'message' => $message,
                'attachment' => "",
                'name' => $name
            );

            $to[] = $recepient;
        }

        //10. send relief email
        /*
        $email_reply = Email::sendMail($from, $to);

        if (!is_null($email_reply))
        {
            foreach ($email_reply as $accname => $a_reply)
            {
                if ($a_reply === 1)
                {
                    $final_result['reliefNotified'][$accname]['emailSent'] = 1;
                }
            }
        }
         * 
         */
        
        //11. delete temp each alternative
        $sql_delete = "delete from temp_each_alternative;";
        $delete_result = Constant::sql_execute($db_con, $sql_delete);
        if (is_null($delete_result))
        {
            throw new DBException('Fail to clear temporary schedules', __FILE__, __LINE__, 2);
        }
        
        //12. return
        uasort($final_result['cancelNotified'], "AdHocSchedulerDB::compareApprove");
        uasort($final_result['reliefNotified'], "AdHocSchedulerDB::compareApprove");
        
        return $final_result;
    }
    
    public static function getApprovedSchedule($scheduleDate)
    {
        //query fullname
        $normal_dict = Teacher::getAllTeachers();
        $temp_dict = Teacher::getTempTeacher("");
        
        //connect
        $db_con = Constant::connect_to_db('ntu');
        if(empty($db_con))
        {
            throw new DBException('Fail to connect to database', __FILE__, __LINE__);
        }
        
        //query
        $sql_query_relief = "SELECT rs_relief_info.*, ct_class_matching.* FROM ((rs_relief_info LEFT JOIN ct_lesson ON ct_lesson.lesson_id = rs_relief_info.lesson_id) LEFT JOIN ct_class_matching ON ct_lesson.lesson_id = ct_class_matching.lesson_id) WHERE rs_relief_info.schedule_date = DATE('".mysql_real_escape_string(trim($scheduleDate))."') order by rs_relief_info.start_time_index, rs_relief_info.end_time_index ASC;";
        
        $relief_result = Constant::sql_execute($db_con, $sql_query_relief);
        if(is_null($relief_result))
        {
            throw new DBException('Fail to query approved schedule on '.$scheduleDate, __FILE__, __LINE__, 2);
        }
        
        //result
        $result = array();
        
        foreach($relief_result as $row)
        {
            $relief_teacher = $row['relief_teacher'];
            
            if(!array_key_exists($relief_teacher, $result))
            {
                if(array_key_exists($relief_teacher, $temp_dict))
                {
                    $relief_name = $temp_dict[$relief_teacher]['fullname'];
                }
                else if(array_key_exists($relief_teacher, $normal_dict))
                {
                    $relief_name = $normal_dict[$relief_teacher]['name'];
                }
                else
                {
                    $relief_name = "";
                }
                
                $result[$relief_teacher] = array(
                    "lesson" => array(),
                    "reliefTeacher" => $relief_name
                );
            }
            
            $relief_id = $row['relief_id'];
            
            if(!array_key_exists($relief_id, $result[$relief_teacher]['lesson']))
            {
                $lesson_id = $row['lesson_id'];
                $start_time_index = $row['start_time_index'];
                $end_time_index = $row['end_time_index'];
                
                //relief not created
                $result[$relief_teacher]['lesson'][$relief_id] = array(
                    'class' => array(),
                    'time' => array($start_time_index, $end_time_index),
                    'lessonID' => $lesson_id,
                    'reliefID' => $relief_id
                );
                
                if(!empty($row['class_name']))
                {
                    $result[$relief_teacher]['lesson'][$relief_id]['class'][] = $row['class_name'];
                }
            }
            else
            {
                //relief created already, the only difference is in class_name
                if(!empty($row['class_name']))
                {
                    $result[$relief_teacher]['lesson'][$relief_id]['class'][] = $row['class_name'];
                }
            }
        }
        
        uasort($result, 'AdHocSchedulerDB::compareName');
        
        return $result;
    }
    
    private static function compareName($a, $b)
    {
        return strcasecmp($a['reliefTeacher'], $b['reliefTeacher']);
    }
    
    private static function compareApprove($a, $b)
    {
        return strcasecmp($a['fullname'], $b['fullname']);
    }
}
?>
