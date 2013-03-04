<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'TimetableDB.php';
require_once 'util.php';
require_once 'ReliefLesson.php';
require_once 'DayTime.php';
require_once 'Lesson.php';

class AdHocSchedulerDB
{
    public static function getReliefPlan($scheduleDate)
    {
        //check data validity
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
        echo $sql_skip;
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
    
    
}
?>
