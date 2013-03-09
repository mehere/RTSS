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
        $sql_get_relief = "select * from rs_relief_info where schedule_date = DATE('$scheduleDate') and relief_id not in (select relief_id from temp_ah_cancelled_relief);";
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
    public static function cancelRelief($reliefID, $startBlockingTime = "", $endBlockingTime = "")
    {
        $AED_list = Teacher::getTeacherInfo("AED");
        
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
        
        //store cancel
        $sql_insert_cancel = "insert into temp_ah_cancelled_relief values ($reliefID, '$relief_teacher', '$schedule_date', $startBlockingTime, $endBlockingTime);";
        $cancel_result = Constant::sql_execute($db_con, $sql_insert_cancel);

        if(is_null($cancel_result))
        {
            throw new DBException('Fail to insert cancel relief ', __FILE__, __LINE__, 2);
        }
        
        //is AED, also need to cancel skip
        if(array_key_exists($relief_teacher, $AED_list))
        {
            $skip_ids = AdHocSchedulerDB::searchSkipForRelief($reliefID);
            
            $sql_insert = "insert into temp_ah cancelled_skip values ";
            foreach($skip_ids as $a_id)
            {
                $sql_insert .= "($a_id, $relief_teacher, $schedule_date),";
            }
            $sql_insert = substr($sql_insert, 0, -1).';';
            
            $cancel_skip = Constant::sql_execute($db_con, $sql_insert);
            if(is_null($cancel_skip))
            {
                throw new DBException('Fail to insert cancel relief ', __FILE__, __LINE__, 2);
            }
        }
    }
    
    public static function searchSkipForRelief($relief_id)
    {
        $db_con = Constant::connect_to_db('ntu');
        if(empty($db_con))
        {
            throw new DBException('Fail to connect to database', __FILE__, __LINE__);
        }
        
        //retrieve relief
        $sql_relief = "select * from rs_relief_info where relief_id = $relief_id;";
        $relief_result = Constant::sql_execute($db_con, $sql_relief);
        if(is_null($relief_result))
        {
            throw new DBException('Fail to retrieve relief info', __FILE__, __LINE__, 2);
        }
        
        if(empty($relief_result))
        {
            return array();
        }
        
        $relief = $relief_result[0];
        
        $schedule_date = $relief['schedule_date'];
        $relief_teacher = $relief['relief_teacher'];
        $relief_start = $relief['start_time_index'];
        $relief_end = $relief['end_time_index'];
        
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
        
        return $recover_list;
    }
    
    public static function adHocApprove($schedule_index, $date)
    {
        $teacher_contact = Teacher::getTeacherContact();
        
        $db_con = Constant::connect_to_db("ntu");
        if (empty($db_con))
        {
            throw new DBException('Fail to approve the schedule', __FILE__, __LINE__);
        }
        
        //1. override old aproved result
        //notify relief
        $sql_old_relief = "select relief_id from temp_ah_cancelled_relief;";
        $old_relief = Constant::sql_execute($db_con, $sql_old_relief);
        if(is_null($old_relief))
        {
            throw new DBException('Fail to clear exist relief record', __FILE__, __LINE__, 2);
        }
        $relief_delete_list = array();
        foreach($old_relief as $row)
        {
            $relief_delete_list[] = $row["relief_id"];
        }
        
        //notify skip
        $sql_override_skip = "select skip_id from temp_ah_cancelled_skip;";
        $override_skip = Constant::sql_execute($db_con, $sql_override_skip);
        if(is_null($override_skip))
        {
            throw new DBException('Fail to clear exist relief record', __FILE__, __LINE__, 2);
        }
        $skip_delete_list = array();
        foreach($override_skip as $row)
        {
            $skip_delete_list[] = $row["skip_id"];
        }
        
        //2. notify
        Notification::sendReliefNotification($schedule_index, $relief_delete_list, $skip_delete_list, $teacher_contact, $date);
        
        //3. clear old result
        $sql_clear_relief = "delete from rs_relief_info where relief_id in (select relief_id from temp_ah_cancelled_relief);";
        $clear_result = Constant::sql_execute($db_con, $sql_clear_relief);
        if (is_null($clear_result))
        {
            throw new DBException('Fail to clear exist relief record', __FILE__, __LINE__, 2);
        }
        
        $sql_clear_skip = "delete from rs_aed_skip_info where skip_id in (select skip_id from temp_ah_cancelled_skip);";
        $clear_skip = Constant::sql_execute($db_con, $sql_clear_skip);
        if (is_null($clear_skip))
        {
            throw new DBException('Fail to clear exist relief record', __FILE__, __LINE__, 2);
        }
        
        $sql_clear_temp = "delete from temp_ah_cancelled_relief;";
        $clear_temp = Constant::sql_execute($db_con, $sql_clear_temp);
        if (is_null($clear_temp))
        {
            throw new DBException('Fail to clear exist relief record', __FILE__, __LINE__, 2);
        }
        
        $sql_clear_temp_skip = "delete from temp_ah_cancelled_relief;";
        $clear_temp_skip = Constant::sql_execute($db_con, $sql_clear_temp_skip);
        if (is_null($clear_temp_skip))
        {
            throw new DBException('Fail to clear exist relief record', __FILE__, __LINE__, 2);
        }
        
        //4. insert new relief
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
        
        //copy approved schedule from temp table and find leave_id_ref for each relief
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

        //5. insert new skip
        //get list of relief to construct skip reference
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

        //6. clear temp tables - relief and skip
        $sql_delete_temp = "delete from temp_each_alternative;";
        $delete_result_temp = Constant::sql_execute($db_con, $sql_delete_temp);
        if (is_null($delete_result_temp))
        {
            throw new DBException('Fail to clear temporary schedules', __FILE__, __LINE__, 2);
        }
        
        $sql_delete_skip = "delete from temp_aed_skip_info;";
        $delete_skip_result = Constant::sql_execute($db_con, $sql_delete_skip);
        if (is_null($delete_skip_result))
        {
            throw new DBException('Fail to clear temporary skip record', __FILE__, __LINE__, 2);
        }
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
        
        //clear previously not approved relief
        $sql_clear_cancel = "delete from temp_ah_cancelled_relief;";
        $clear_cancel = Constant::sql_execute($db_con, $sql_clear_cancel);
        if(is_null($clear_cancel))
        {
            throw new DBException("Fail to clear cancelled relief", __FILE__, __LINE__);
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
