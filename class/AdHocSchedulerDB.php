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
require_once 'Teacher.php';

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
    
    public static function cancelRelief($reliefID, $startBlockingTime, $endBlockingTime)
    {
        $db_con = Constant::connect_to_db('ntu');
        if(empty($db_con))
        {
            throw new DBException('Fail to connect to database', __FILE__, __LINE__);
        }
        
        //retrieve leave
        $sql_relief = "select * from rs_relief_info where relief_id = $reliefID;";
        $relief_result = Constant::sql_execute($db_con, $sql_relief);
        if(is_null($relief_result))
        {
            throw new DBException('Retrieve the latest code');
        }
        
        //if(empty())
        
        //store cancel
        
        //search for rs_aed_skip
        
        //delete relief
        
        //delete skip - error : restore relief
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
}
?>
