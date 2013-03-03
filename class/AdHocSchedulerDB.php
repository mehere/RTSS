<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'TimetableDB.php';
require_once 'util.php';
require_once 'ReliefLesson.php';

class AdHocSchedulerDB
{
    public static function getReliefPlan($scheduleDate)
    {
        //check data validity
        $sem_id = TimetableDB::checkTimetableExistence(0, array('date'=>$scheduleDate));
        if($sem_id === -1)
        {
            throw new DBException('No lesson information on '.$scheduleDate, __FILE__, __LINE__, 1);
        }
        
        //db connection
        $db_con = Constant::connect_to_db("ntu");
        if(empty($db_con))
        {
            throw new DBException('Fail to connect to database'.$scheduleDate, __FILE__, __LINE__);
        }
    
        //query
        $sql_get_relief = "select * from rs_relief_info where schedule_date = DATE($scheduleDate);";
        $relief_result = Constant::sql_execute($db_con, $sql_get_relief);
        if(is_null($relief_result))
        {
            throw new DBException('Fail to query relief'.$scheduleDate, __FILE__, __LINE__, 2);
        }
        
        //return data structure cnotruction
        $result = array();
        /*
        foreach($relief_result as $row)
        {
            
            $a_relief = new ReliefLesson($aTeacherAccname, $aLessonId, $startTimeIndex)
        }
         * 
         */
    }
    
    
}
?>
