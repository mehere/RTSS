<?php
class ReportDB
{
    public static function getReasonList($type = "", $order = "fullname", $direction = SORT_ASC, $year = "2013", $sem = 1)
    {
        $result = array();

        $db_con = Constant::connect_to_db("ntu");
        if(empty($db_con))
        {
            throw new DBException('Fail to query report', __FILE__, __LINE__);
        }

        $mc_dic = array();
        
        /*
         * disable future leave option
        if($future_leave)
        {
            $sql_query_mc = "select teacher_id, sum(num_of_slot) as num_of_leave from (select rs_leave_info.* from rs_leave_info, ct_semester_info where ct_semester_info.year = '$year' and ct_semester_info.sem_num = $sem and (DATE(rs_leave_info.start_time) between ct_semester_info.start_date and ct_semester_info.end_date)) AS temp_leave group by teacher_id;";
            $query_mc_result = Constant::sql_execute($db_con, $sql_query_mc);
            if(is_null($query_mc_result))
            {
                throw new DBException('Fail to query report', __FILE__, __LINE__, 2);
            }
            
            foreach($query_mc_result as $row)
            {
                $mc_dic[$row["teacher_id"]] = $row["num_of_leave"];
            }
        }
        else
        {
         * 
         */
            //trim future leave
            $sql_query_mc = "select rs_leave_info.*, DATE_FORMAT(start_time, '%Y/%m/%d %H:%i') as start_datetime, DATE_FORMAT(end_time, '%Y/%m/%d %H%i') as end_datetime from rs_leave_info, ct_semester_info where ct_semester_info.year = '$year' and ct_semester_info.sem_num = $sem and (DATE(rs_leave_info.start_time) between ct_semester_info.start_date and ct_semester_info.end_date);";
            $query_mc_result = Constant::sql_execute($db_con, $sql_query_mc);
            if(is_null($query_mc_result))
            {
                throw new DBException('Fail to query report', __FILE__, __LINE__, 2);
            }
            
            $today = new DateTime();
            $asia_timezone = new DateTimeZone("Asia/Singapore");
            $today->setTimezone($asia_timezone);
            $today_stamp = $today->getTimestamp();
            
            foreach($query_mc_result as $row)
            {
                $start_datetime_str = $row['start_datetime'];
                $start_date = new DateTime($start_datetime_str);
                $start_stamp = $start_date->getTimestamp();

                if($start_stamp > $today_stamp)
                {
                    continue;
                }
             
                $accname = $row['teacher_id'];
                if(!array_key_exists($accname, $mc_dic))
                {
                    $mc_dic[$accname]['count'] = 0;
                    $mc_dic[$accname]['reason'] = array();
                }
                
                $end_date_str = $row['end_datetime'];
                $end_date = new DateTime($end_date_str);
                $end_date_stamp = $end_date->getTimestamp();
                
                if($end_date_stamp > $today_stamp)
                {
                    //need to trim
                    $start_date_str = $row['start_datetime'];
                    $trimed_slot = Teacher::calculateLeaveSlot($accname, $start_date_str, $end_date_str);
                    
                    $mc_dic[$accname]['count'] += $trimed_slot;
                }
                else
                {
                    //no future leave to trim
                    $trimed_slot = $row["num_of_slot"];
                    $mc_dic[$accname]['count'] += $trimed_slot;
                }
                
                $reason_str = $row['reason'];
                
                if(empty($mc_dic[$accname]['reason'][$reason_str]))
                {
                    $mc_dic[$accname]['reason'][$reason_str] = $trimed_slot;
                }
                else
                {
                    $mc_dic[$accname]['reason'][$reason_str] += $trimed_slot;
                }
            }
        //}
        
        $relief_dic = array();
        /*
         * disable future leave options
        if($future_leave)
        {
            $sql_query_relief = "select relief_teacher, sum(num_of_slot) as num_of_relief from (select rs_relief_info.* from rs_relief_info, ct_semester_info where ct_semester_info.year = '$year' and ct_semester_info.sem_num = $sem and (DATE(rs_relief_info.schedule_date) between ct_semester_info.start_date and ct_semester_info.end_date)) AS temp_relief group by relief_teacher;";
        }
        else
        {
         * 
         */
            $sql_query_relief = "select relief_teacher, sum(num_of_slot) as num_of_relief from (select rs_relief_info.* from rs_relief_info, ct_semester_info where ct_semester_info.year = '$year' and ct_semester_info.sem_num = $sem and (DATE(rs_relief_info.schedule_date) between ct_semester_info.start_date and ct_semester_info.end_date) and DATE(rs_relief_info.schedule_date) < DATE(NOW())) AS temp_relief group by relief_teacher;";
        //}
        
        $query_relief_result = Constant::sql_execute($db_con, $sql_query_relief);
        if(is_null($query_relief_result))
        {
            throw new DBException('Fail to query report', __FILE__, __LINE__, 2);
        }
        foreach($query_relief_result as $row)
        {
            $relief_dic[$row["relief_teacher"]] = $row["num_of_relief"];
        }

        $teacher_dict = Teacher::getTeacherName($type);
        foreach($teacher_dict as $a_teacher)
        {
            $a_record = Array();

            $a_record['accname'] = $a_teacher['accname'];
            $a_record['fullname'] = $a_teacher['fullname'];
            $a_record['type'] = $a_teacher['type'];

            if(array_key_exists($a_record['accname'], $mc_dic))
            {
                $a_record['numOfMC'] = $mc_dic[$a_record['accname']]['count'];
                $a_record['reason'] = $mc_dic[$a_record['accname']]['reason'];
            }
            else
            {
                $a_record['numOfMC'] = 0;
                $a_record['reason'] = array();
            }

            if(array_key_exists($a_record['accname'], $relief_dic))
            {
                $a_record['numOfRelief'] = $relief_dic[$a_record['accname']];
            }
            else
            {
                $a_record['numOfRelief'] = 0;
            }

            $a_record['net'] = $a_record['numOfMC'] - $a_record['numOfRelief'];

            $a_record['reason'] = 
            
            $result[] =$a_record;
        }

        //sort
        $sort_arr = array();

        foreach($result as $key=>$value)
        {
            $sort_arr[$key] = $value[$order];
        }

        array_multisort($sort_arr, $direction, $result);

        return $result;
    }
}
?>
