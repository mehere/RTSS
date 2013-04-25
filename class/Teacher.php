<?php
spl_autoload_register(function($class){
    require_once "$class.php";
});

class Teacher {

    //put your code here
    public $abbreviation;
    public $name;
    public $accname;
    public $noLessonMissed;
    public $noLessonRelived;
    public $leave;
    public $availability;
    public $timetable;
    public $isHighlighted;
    public $speciality;
    public $classes;

    public function __construct($abbreviation) {
        $this->abbreviation = $abbreviation;
        $this->name = NULL;
        $this->accname = NULL;
        $this->timetable = array();
        $this->noLessonMissed = 0;
        $this->noLessonRelived = 0;
        $this->leave = array();
        $this->availability = array();
        $this->isHighlighted = true;
        $this->speciality = array();
        $this->classes = null;
    }

    /**
     * This function finds accname and fullname of teachers. Pass by reference
     * @param Array $teacher_list
     * @return boolean
     */
    public static function getTeachersAccnameAndFullname(&$teacher_list)
    {
        //get abbre-accname list
        $db_con = Constant::connect_to_db('ntu');

        if (empty($db_con))
        {
            throw new DBException("Fail to connect to database", __FILE__, __LINE__);
        }

        $sql_query = "select * from ct_name_abbre_matching;";
        $result = Constant::sql_execute($db_con, $sql_query);
        if(is_null($result))
        {
            throw new DBException("Fail to query abbre accname match", __FILE__, __LINE__, 2);
        }

        $abbre_dict = Array();
        foreach($result as $row)
        {
            $abbre_dict[str_replace(" ", "_", $row['abbre_name'])] = $row['teacher_id'];
        }

        //get accname - teacher list
        $teacher_dict = Teacher::getAllTeachers();

        //search teacher name
        foreach($teacher_list as $key => $a_teacher)
        {
            if(!empty($abbre_dict[str_replace(" ", "_", $a_teacher->abbreviation)]))
            {
                $a_teacher->accname=$abbre_dict[str_replace(" ", "_", $a_teacher->abbreviation)];
                $a_teacher->name=$teacher_dict[$a_teacher->accname]['name'];
            }

            $teacher_list[$key] = $a_teacher;
        }

        return true;
    }

    //this function returns all teachers on leave today
    //input : date string, in format 2012-12-11
    //output : array of associative arrays each representing a piece of leave info that's on the input date. Empty - possibly there are errors. Check database for confirmation.
    public static function getTeacherOnLeave($query_date)
    {
        $result = Array();

        //query teacher dict
        $teacher_dict = Teacher::getAllTeachers();

        $db_con = Constant::connect_to_db('ntu');

        if (empty($db_con))
        {
            throw new DBException('Fail to query teacher on leave', __FILE__, __LINE__);
        }

        //start
        //query relief info to check whether scheduled
        $sql_query_scheduled = "select * from rs_leave_scheduled where schedule_date = '".mysql_real_escape_string($query_date)."';";
        $scheduled_query_result = Constant::sql_execute($db_con, $sql_query_scheduled);
        if(is_null($scheduled_query_result))
        {
            throw new DBException("Fail to query leave information", __FILE__, __LINE__, 2);
        }

        $leave_id_array = array();
        foreach($scheduled_query_result as $row)
        {
            $one_leave_id = $row['leave_id'];
            if(!in_array($one_leave_id, $leave_id_array))
            {
                $leave_id_array[] = $one_leave_id;
            }
        }
         
        //query leave
        $sql_query_leave = "select *, DATE_FORMAT(rs_leave_info.start_time, '%Y/%m/%d') as start_date, DATE_FORMAT(rs_leave_info.end_time, '%Y/%m/%d') as end_date, TIME_FORMAT(rs_leave_info.start_time, '%H:%i') as start_time_point, TIME_FORMAT(rs_leave_info.end_time, '%H:%i') as end_time_point from rs_leave_info
            where DATE('".mysql_real_escape_string(trim($query_date))."') between date(rs_leave_info.start_time) and date(rs_leave_info.end_time);";

        $query_leave_result =  Constant::sql_execute($db_con, $sql_query_leave);

        if(is_null($query_leave_result))
        {
            throw new DBException('Fail to query teacher on leave', __FILE__, __LINE__, 2);
        }

        foreach($query_leave_result as $row)
        {
            $each_record = Array();
            $each_record['accname'] = $row['teacher_id'];
            $each_record['reason'] = $row['reason'];
            $each_record['remark'] = empty($row['remark'])?'':$row['remark'];
            $each_record['leaveID'] = $row['leave_id'];
            $each_record['isVerified'] = ($row['verified'] === 'YES')?true:false;
            $each_record['datetime'] = Array(Array($row['start_date'], $row['start_time_point']), Array($row['end_date'], $row['end_time_point']));
            $each_record['isScheduled'] = false;
            if(in_array($each_record['leaveID'], $leave_id_array))
            {
                $each_record['isScheduled'] = true;
            }
            
            $each_record['fullname'] = empty($teacher_dict[$row['teacher_id']])?"":$teacher_dict[$row['teacher_id']]['name'];
            if(strcmp(substr($each_record['accname'], 0, 3), "TMP") === 0)
            {
                $each_record['type'] = Constant::$teacher_type[2];
            }
            else
            {
                $each_record['type'] = empty($teacher_dict[$row['teacher_id']])?"Teacher not found":$teacher_dict[$row['teacher_id']]['type'];
            }
            $each_record['handphone'] = empty($teacher_dict[$row['teacher_id']])?"Teacher not found":$teacher_dict[$row['teacher_id']]['mobile'];

            $result[] = $each_record;
        }
        //end

        return $result;
    }

    //This function get all temporary teachers
    //input : date string, in format yyyy-mm-dd
    //output : array of associative arrays each representing temporary teacher. MT, remark, email may be ""
    public static function getTempTeacher($query_date)
    {
        $result = array();

        $db_con = Constant::connect_to_db("ntu");

        if (empty($db_con))
        {
            throw new DBException("Fail to query temporary teachers", __FILE__, __LINE__);
        }

        if(!empty($query_date))
        {
            $sql_query_temp_teacher = "select *, DATE_FORMAT(rs_temp_relief_teacher_availability.start_datetime, '%Y/%m/%d') as start_date, DATE_FORMAT(rs_temp_relief_teacher_availability.end_datetime, '%Y/%m/%d') as end_date, TIME_FORMAT(rs_temp_relief_teacher_availability.start_datetime, '%H:%i') as start_time, TIME_FORMAT(rs_temp_relief_teacher_availability.end_datetime, '%H:%i') as end_time
                from rs_temp_relief_teacher_availability, rs_temp_relief_teacher where rs_temp_relief_teacher_availability.teacher_id=rs_temp_relief_teacher.teacher_id and '".mysql_real_escape_string($query_date)."' between date(rs_temp_relief_teacher_availability.start_datetime) and date(rs_temp_relief_teacher_availability.end_datetime);";
        }
        else
        {
            $sql_query_temp_teacher = "select * from rs_temp_relief_teacher;";
        }

        $query_temp_teacher = Constant::sql_execute($db_con, $sql_query_temp_teacher);

        if(is_null($query_temp_teacher))
        {
            throw new DBException("Fail to query temporary teachers", __FILE__, __LINE__, 2);
        }

        foreach($query_temp_teacher as $row)
        {
            $one_teacher['accname'] = $row['teacher_id'];
            $one_teacher['fullname'] = $row['name'];
            $one_teacher['type'] = "Temporary";
            $one_teacher['MT'] = (empty($row['mother_tongue'])?'':$row['mother_tongue']);
            $one_teacher['email'] = (empty($row['email'])?'':$row['email']);
            $one_teacher['handphone'] = (empty($row['mobile'])?'':$row['mobile']);

            if(!empty($query_date))
            {
                $one_teacher['datetime'] = Array(Array($row['start_date'], $row['start_time']), Array($row['end_date'], $row['end_time']));
                $one_teacher['remark'] = (empty($row['slot_remark'])?'':$row['slot_remark']);
                $one_teacher['availability_id'] = $row['temp_availability_id'];

                $result[] = $one_teacher;
            }
            else
            {
                $result[$row['teacher_id']] = $one_teacher;
            }
        }

        return $result;
    }
    /**
     *
     * @param string $type : "", "temp", "all_normal", "normal", "AED", "untrained", "HOD", "ExCo", "executive", "non-executive", "all_normal_except_aed"
     * @return array
     */
    public static function getTeacherName($type)
    {
        $normal_list = array();
        $temp_list = array();

        $db_type = array_keys(Constant::$teacher_type);

        if(empty($type) || strcmp($type, "temp")!==0)
        {
            $ifins_db_con = Constant::connect_to_db("ifins");

            if (empty($ifins_db_con))
            {
                throw new DBException("Fail to query teachers", __FILE__, __LINE__);
            }

            if(empty($type) || strcmp($type, "all_normal")===0)
            {
                $sql_query_normal = "select user_id, user_name, dept_name from student_details where user_position = 'Teacher' and dept_name in ('".$db_type[0]."', '".$db_type[1]."', '".$db_type[3]."', '".$db_type[5]."', '".$db_type[4]."') order by user_name;";
            }
            else
            {
                if(strcmp($type, "normal")===0)
                {
                    $sql_query_normal = "select user_id, user_name, dept_name from student_details where user_position = 'Teacher' and dept_name = '".$db_type[0]."' order by user_name;";
                }
                if(strcmp($type, "AED")===0)
                {
                    $sql_query_normal = "select user_id, user_name, dept_name from student_details where user_position = 'Teacher' and dept_name = '".$db_type[1]."' order by user_name;";
                }
                if(strcmp($type, "untrained")===0)
                {
                    $sql_query_normal = "select user_id, user_name, dept_name from student_details where user_position = 'Teacher' and dept_name = '".$db_type[4]."' order by user_name;";
                }
                if(strcmp($type, "HOD")===0)
                {
                    $sql_query_normal = "select user_id, user_name, dept_name from student_details where user_position = 'Teacher' and dept_name = '".$db_type[3]."' order by user_name;";
                }
                if(strcmp($type, "ExCo")===0)
                {
                    $sql_query_normal = "select user_id, user_name, dept_name from student_details where user_position = 'Teacher' and dept_name = '".$db_type[5]."' order by user_name;";
                }
                if(strcmp($type, "non-executive")===0)
                {
                    $sql_query_normal = "select user_id, user_name, dept_name from student_details where user_position = 'Teacher' and dept_name in ('".$db_type[0]."', '".$db_type[1]."', '".$db_type[4]."') order by user_name;";
                }
                if(strcmp($type, "executive")===0)
                {
                    $sql_query_normal = "select user_id, user_name, dept_name from student_details where user_position = 'Teacher' and dept_name in ('".$db_type[3]."', '".$db_type[5]."') order by user_name;";
                }
                if(strcmp($type, "all_normal_except_aed")===0)
                {
                    $sql_query_normal = "select user_id, user_name, dept_name from student_details where user_position = 'Teacher' and dept_name != '$db_type[1]' order by user_name;";
                }
            }

            $query_normal_result = Constant::sql_execute($ifins_db_con, $sql_query_normal);

            if(is_null($query_normal_result))
            {
                throw new DBException("Fail to query teachers", __FILE__, __LINE__, 2);
            }
            
            foreach($query_normal_result as $row)
            {
                $normal_list[] = Array(
                    'fullname' => $row['user_name'],
                    'accname' => $row['user_id'],
                    'type' => $row['dept_name']
                );
            }
        }
        if(empty($type) || strcmp($type, "temp")===0)
        {
            $db_con = Constant::connect_to_db("ntu");

            if (!$db_con)
            {
                throw new DBException("Fail to query teachers", __FILE__, __LINE__);
            }

            $sql_query_temp = "select teacher_id, name from rs_temp_relief_teacher order by name;";
            $query_temp_result = Constant::sql_execute($db_con, $sql_query_temp);

            if(is_null($query_temp_result))
            {
                throw new DBException("Fail to query teachers", __FILE__, __LINE__, 2);
            }
            
            foreach($query_temp_result as $row)
            {
                $temp_list[] = Array(
                    'fullname' => $row['name'],
                    'accname' => $row['teacher_id'],
                    'type' => 'Temp'
                );
            }
        }

        $result_array = array_merge($normal_list, $temp_list);
        if(empty($type))
        {
            foreach($result_array as $key=>$value)
            {
                $fullname[$key] = $value['fullname'];
            }

            array_multisort($fullname, SORT_ASC, $result_array);
        }

        return $result_array;
    }

    /**
     *
     * @param string $type "executive" : HOD and ExCo; "non-executive" : others
     * @return associative array key : accname
     */
    public static function getTeacherInfo($type)
    {
        $result = Array();

        $in_array = Teacher::getTeacherName($type);

        foreach($in_array as $value)
        {
            $result[$value['accname']] = Array();
            $result[$value['accname']]['fullname'] = $value['fullname'];
            $result[$value['accname']]['type'] = $value['type'];
        }

        return $result;
    }

    //this function returns the details of a normal teacher
    //input : accname - the name used to log in
    //output : associative array of information. Before retrieving any information, check if($output['found']) to see whether the teacher record is found
    public static function getIndividualTeacherDetail($accname)
    {
        $result = Array(
            'found' => false,
            'ID' => $accname,
        );

        if(substr($accname, 0, 3) === 'TMP')
        {
            $db_con = Constant::connect_to_db('ntu');

            if (empty($db_con))
            {
                return $result;
            }

            //with full name, query information from ifins_2012.actatek_user
            $sql_query_detail = "select * from rs_temp_relief_teacher where teacher_id = '".mysql_real_escape_string(trim($accname))."';";
            $query_result = Constant::sql_execute($db_con, $sql_query_detail);

            if(empty($query_result))
            {
                return $result;
            }

            $row = $query_result[0];
            if(!$row)
            {
                return $result;
            }

            $result['found'] = true;
            $result['name'] = $row['name'];
            $result['gender'] = $row['gender'];
            $result['handphone'] = $row['mobile'];
            $result['email'] = $row['email'];

            return $result;
        }
        else
        {
            $ifins_db_con = Constant::connect_to_db('ifins');
            
            if (empty($ifins_db_con))
            {
                return $result;
            }

            //with full name, query information from ifins_2012.actatek_user
            $sql_query_detail = "select * from actatek_user where user_id = '".mysql_real_escape_string(trim($accname))."' and user_position = 'Teacher';";
            $ifins_query_result = Constant::sql_execute($ifins_db_con, $sql_query_detail);

            if(empty($ifins_query_result))
            {
                return $result;
            }

            $ifins_row = $ifins_query_result[0];
            if(!$ifins_row)
            {
                return $result;
            }

            $result['found'] = true;
            $result['name'] = $ifins_row['user_name'];
            $result['gender'] = $ifins_row['user_gender'];
            $result['handphone'] = $ifins_row['user_mobile'];
            $result['email'] = $ifins_row['user_email'];

            return $result;
        }
    }

    /**
     *
     * @param array $list : all excluded teachers, including default one
     * @return bool
     */
    public static function setExcludingList($date, $list)
    {
        if(!isset($_SESSION['excluded']))
        {
            $_SESSION['excluded'] = Array();
        }

        $_SESSION['excluded'][$date] = implode(",", $list);

        if(count($list) === 0)
        {
            $_SESSION['excluded'][$date] = "emp";
        }
        
        return true;
    }

    public static function getExcludingList($date)
    {
        if(!isset($_SESSION['excluded']) || empty($_SESSION['excluded'][$date]))
        {
            $db_con = Constant::connect_to_db("ntu");
            if(empty($db_con))
            {
                throw new DBException("Fail to get exclude list", __FILE__, __LINE__);
            }
            $sql_query_exclude = "select * from rs_exclude_list;";
            $query_exclude_result = Constant::sql_execute($db_con, $sql_query_exclude);
            if(is_null($query_exclude_result))
            {
                throw new DBException("Fail to get exclude list", __FILE__, __LINE__, 2);
            }

            $result = Array();

            foreach($query_exclude_result as $row)
            {
                $result[] = $row['teacher_id'];
            }

            return $result;
        }
        else if(strcmp($_SESSION['excluded'][$date], "emp") === 0)
        {
            return array();
        }
        else
        {
            return explode(",", $_SESSION['excluded'][$date]);
        }
    }

    public static function leaveHasRelief($accname, $entry)
    {
        $start_datetime = $entry['datetime-from'];
        $end_datetime = $entry['datetime-to'];
        
        $start_arr = explode(" ", $start_datetime);
        $start_date = $start_arr[0];
        $start_time = $start_arr[1];
        $start_index = SchoolTime::getApproTimeIndex($start_time);
        $end_arr = explode(" ", $end_datetime);
        $end_date = $end_arr[0];
        $end_time = $end_arr[1];
        $end_index = SchoolTime::getApproTimeIndex($end_time);
        
        $start_obj = new DateTime($start_date);
        $end_obj = new DateTime($end_date);
        $diff = $start_obj->diff($end_obj);
        
        if($diff->d + $diff->m + $diff->y === 0)
        {
            $sql_check = "select * from rs_relief_info where DATE(schedule_date) = DATE('$start_datetime') and start_time_index < $end_index and end_time_index > $start_index and relief_teacher = '$accname';";
        }
        else
        {
            $sql_check = "select * from rs_relief_info where ((DATE(schedule_date) > DATE('$start_datetime') and DATE(schedule_date) < DATE('$end_datetime')) or (DATE(schedule_date) = DATE('$start_datetime') and end_time_index > $start_index) or (DATE(schedule_date) = DATE('$end_datetime') and start_time_index < $end_index)) and relief_teacher = '$accname';";
        }

        $db_con = Constant::connect_to_db("ntu");
        if(empty($db_con))
        {
            return true;
        }
        
        $check = Constant::sql_execute($db_con, $sql_check);
        if(is_null($check))
        {
            return true;
        }
        if(count($check) > 0)
        {
            return true;
        }
        
        return false;
    }
    
    /**
     * add leave and temp teacher
     * @param string $accname
     * @param type $prop : leave or temp
     * @param type $entry : associative array specified in docs
     * @return type int >=0: leaveID or availabilityID, <0: error (desc: -2 db connect error; -3 lack of necessary value; -4 db insert error; -5 rarely returned. but if return, email me, -6 conflict time)
     */
    public static function add($accname, $prop, $entry, $has_relief, $leaveID = -1)
    {        
        $db_con = Constant::connect_to_db('ntu');

        if (empty($db_con))
        {
            return -2;
        }

        if(strcmp($prop, "leave")===0)
        {
            if(empty($accname) || empty($entry['datetime-from']) || empty($entry['datetime-to']))
            {
                return -3;
            }

            //check conflict
            $clean_datetime_from = mysql_real_escape_string(trim($entry['datetime-from']));
            $clean_datetime_to = mysql_real_escape_string(trim($entry['datetime-to']));
            $clean_accname = mysql_real_escape_string(trim($accname));
            
            $sql_check_conflict = "select * from rs_leave_info where teacher_id = '$clean_accname' and (unix_timestamp(start_time) < unix_timestamp('$clean_datetime_to') && unix_timestamp(end_time) > unix_timestamp('$clean_datetime_from'));";
            $check_conflict = Constant::sql_execute($db_con, $sql_check_conflict);
            if(is_null($check_conflict))
            {
                return -2;
            }
            if(!empty($check_conflict))
            {
                return -6;
            }
            
            //delete linked relief if any
            if($has_relief)
            {
                $teacher_contact = Teacher::getTeacherContact();
                $AED_list = Teacher::getTeacherName("AED");
                $is_AED = false;
                if(array_key_exists($accname, $AED_list))
                {
                    $is_AED = true;
                }
                
                $db_con = Constant::connect_to_db('ntu');

                if (empty($db_con))
                {
                    return -2;
                }
                
                $start_datetime = $entry['datetime-from'];
                $end_datetime = $entry['datetime-to'];

                $start_arr = explode(" ", $start_datetime);
                $start_date = $start_arr[0];
                $start_time = $start_arr[1];
                $start_index = SchoolTime::getApproTimeIndex($start_time);
                $end_arr = explode(" ", $end_datetime);
                $end_date = $end_arr[0];
                $end_time = $end_arr[1];
                $end_index = SchoolTime::getApproTimeIndex($end_time);

                $start_obj = new DateTime($start_date);
                $end_obj = new DateTime($end_date);
                $diff = $start_obj->diff($end_obj);

                if($diff->d + $diff->m + $diff->y === 0)
                {
                    $sql_check = "select * from rs_relief_info where DATE(schedule_date) = DATE('$start_datetime') and start_time_index < $end_index and end_time_index > $start_index and relief_teacher = '$accname';";
                }
                else
                {
                    $sql_check = "select * from rs_relief_info where ((DATE(schedule_date) > DATE('$start_datetime') and DATE(schedule_date) < DATE('$end_datetime')) or (DATE(schedule_date) = DATE('$start_datetime') and end_time_index > $start_index) or (DATE(schedule_date) = DATE('$end_datetime') and start_time_index < $end_index)) and relief_teacher = '$accname';";
                }

                $check = Constant::sql_execute($db_con, $sql_check);
                if(is_null($check))
                {
                    return false;
                }
                
                $time_zone = new DateTimeZone('Asia/Singapore');
                $today_obj = new DateTime();
                $today_obj->setTimezone($time_zone);
                $today_str = $today_obj->format("Y-m-d");
                $today = strtotime($today_str);
                $now_time_str = $today_obj->format('H:i');
                $now_index = SchoolTime::getApproTimeIndex($now_time_str);
                
                $affected_relief = array();
                $affected_skip = array();
                $notified_relief = array();
                $notified_skip = array();
                $affected_leave = array();
                foreach($check as $row)
                {
                    $affected_relief[] = $row["relief_id"];
                    $relief_date = strtotime($row["schedule_date"]);
                    $start_relief = $row["start_time_index"] - 0;
                    
                    if($relief_date > $today || ($relief_date === $today && $start_relief > $now_index))
                    {
                        $notified_relief[] = $row["relief_id"];
                    }
                    
                    $affected_leave = array($row["leave_id_ref"], $row["schedule_date"]);
                    
                    if($is_AED)
                    {
                        $skips = AdHocSchedulerDB::searchSkipForRelief($row["relief_id"]);
                        
                        foreach($skips as $one_skip)
                        {
                            $affected_skip[] = $one_skip;
                            if($relief_date > $today || ($relief_date === $today && $start_relief > $now_index))
                            {
                                $notified_skip[] = $one_skip;
                            }
                        }
                    }
                }
                
                if(count($notified_relief) > 0 || count($notified_skip) > 0)
                {
                    Notification::sendCancelNotification($notified_relief, $notified_skip, $teacher_contact, "");
                }
                
                if(count($affected_relief) > 0)
                {
                    //delete skip 
                    $sql_delete_relief = "delete from rs_relief_info where relief_id in (".  implode(', ', $affected_relief).");";
                    $delete_relief_result = Constant::sql_execute($db_con, $sql_delete_relief);
                    if(is_null($delete_relief_result))
                    {
                        return false;
                    }
                }
                
                if(count($affected_skip) > 0)
                {
                    //delete skip 
                    $sql_delete_skip = "delete from rs_aed_skip_info where skip_id in (".  implode(', ', $affected_skip).");";
                    $delete_skip_result = Constant::sql_execute($db_con, $sql_delete_skip);
                    if(is_null($delete_skip_result))
                    {
                        return false;
                    }
                }
                error_log(print_r($affected_leave, true));
                if(count($affected_leave) > 0)
                {
                    //delete is scheduled
                    $sql_delete_leave_scheduled = "delete from rs_leave_scheduled where ";
                    foreach($affected_leave as $a_leave)
                    {
                        $sql_delete_leave_scheduled .= "(leave_id = $a_leave[0] and schedule_date = DATE('$a_leave[1]')) or ";
                    }
                    $sql_delete_leave_scheduled = substr($sql_delete_leave_scheduled, 0, -4).';';
                    $delete_scheduled = Constant::sql_execute($db_con, $sql_delete_leave_scheduled);
                    if(is_null($delete_scheduled))
                    {
                        throw new DBException('Fail to delete relief', __FILE__, __LINE__);
                    }
                }
            }
            
            //insert
            $reason = empty($entry['reason'])?'':$entry['reason'];
            $remark = empty($entry['remark'])?'':$entry['remark'];

            $num_of_slot = Teacher::calculateLeaveSlot($clean_accname, $clean_datetime_from, $clean_datetime_to);

            if($leaveID === -1)
            {
                $sql_insert_leave = "insert into rs_leave_info(teacher_id, reason, remark, start_time, end_time, verified, num_of_slot) values
                    ('".$clean_accname."', '".mysql_real_escape_string(trim($reason))."', '".mysql_real_escape_string(trim($remark))."',
                        '".$clean_datetime_from."', '".$clean_datetime_to."', 'NO', ".$num_of_slot.");";
            }
            else
            {
                $sql_insert_leave = "insert into rs_leave_info(leave_id, teacher_id, reason, remark, start_time, end_time, verified, num_of_slot) values
                    ($leaveID, '".$clean_accname."', '".mysql_real_escape_string(trim($reason))."', '".mysql_real_escape_string(trim($remark))."',
                        '".$clean_datetime_from."', '".$clean_datetime_to."', 'NO', ".$num_of_slot.");";
            }
            

            $insert_leave_result = Constant::sql_execute($db_con, $sql_insert_leave);

            if(is_null($insert_leave_result))
            {
                return -4;
            }

            return mysql_insert_id();
        }
        else if(strcmp($prop, "temp")===0)
        {
            if(empty($entry['datetime-from']) || empty($entry['datetime-to']))
            {
                return -3;
            }

            $accname = mysql_real_escape_string(trim($accname));
            
            if(empty($accname) || (!empty($accname) && $leaveID != -1))
            {
                if(empty($entry['fullname']))
                {
                    return -3;
                }
                
                $fullname = $entry['fullname'];
                
                if(empty($accname))
                {
                    $name_array = explode(" ", $fullname);
                    if(count($name_array)>0)
                    {
                        if(strlen($name_array[0])>2)
                        {
                            $name_short = substr($name_array[0], 0, 3);
                        }
                        else if(strlen($name_array[0])==2)
                        {
                            $name_short = $name_array[0].rand(0, 9);
                        }
                        else if(strlen($name_array[0])==1)
                        {
                            $name_short = $name_array[0].rand(11, 99);
                        }
                        else
                        {
                            return -5;
                        }
                    }
                    else
                    {
                        return -3;
                    }

                    //time since 2010-1-1 00:00:00
                    $accname = "TMP".(time() - 1261440000).$name_short;
                }
                else
                {
                    $sql_clear_temp = "delete from rs_temp_relief_teacher where teacher_id = '$accname';";
                    $clear_temp = Constant::sql_execute($db_con, $sql_clear_temp);
                    if(is_null($clear_temp))
                    {
                        return -2;
                    }
                }
                
                $handphone = empty($entry['handphone'])?'':$entry['handphone'];
                $email = empty($entry['email'])?'':$entry['email'];
                $MT = empty($entry['MT'])?'':$entry['MT'];

                $sql_insert_temp_teacher = "insert into rs_temp_relief_teacher(teacher_id, name, mobile, email, mother_tongue) values
                    ('".mysql_real_escape_string(trim($accname))."', '".mysql_real_escape_string(trim($fullname))."', '".mysql_real_escape_string(trim($handphone))."',
                        '".mysql_real_escape_string(trim($email))."', '".mysql_real_escape_string(trim($MT))."');";

                $insert_temp_result = Constant::sql_execute($db_con, $sql_insert_temp_teacher);

                if(!$insert_temp_result)
                {
                    return -2;
                }
            }
            
            $clean_datetime_from = mysql_real_escape_string(trim($entry['datetime-from']));
            $clean_datetime_to = mysql_real_escape_string(trim($entry['datetime-to']));
            
            $sql_check_conflict = "select * from rs_temp_relief_teacher_availability where teacher_id = '$accname' and (unix_timestamp(start_datetime) < unix_timestamp('$clean_datetime_to') && unix_timestamp(end_datetime) > unix_timestamp('$clean_datetime_from'));";
            $check_conflict = Constant::sql_execute($db_con, $sql_check_conflict);
            if(is_null($check_conflict))
            {
                return -2;
            }
            if(!empty($check_conflict))
            {
                return -6;
            }
            
            $temp_remark = empty($entry['remark'])?'':$entry['remark'];

            if($leaveID === -1)
            {
                $sql_insert_temp_time = "insert into rs_temp_relief_teacher_availability(teacher_id, start_datetime, end_datetime, slot_remark) values
                ('".mysql_real_escape_string(trim($accname))."', '".mysql_real_escape_string(trim($entry['datetime-from']))."',
                    '".mysql_real_escape_string(trim($entry['datetime-to']))."', '".mysql_real_escape_string(trim($temp_remark))."');";
            }
            else
            {
                $sql_insert_temp_time = "insert into rs_temp_relief_teacher_availability(temp_availability_id, teacher_id, start_datetime, end_datetime, slot_remark) values
                ($leaveID, '".mysql_real_escape_string(trim($accname))."', '".mysql_real_escape_string(trim($entry['datetime-from']))."',
                    '".mysql_real_escape_string(trim($entry['datetime-to']))."', '".mysql_real_escape_string(trim($temp_remark))."');";
            }

            $insert_temp_time_result = Constant::sql_execute($db_con, $sql_insert_temp_time);

            if(!$insert_temp_time_result)
            {
                return -4;
            }
            
            return mysql_insert_id();
        }
        else
        {
            return -3;
        }
    }

    public static function checkHasRelief($leaveIDList, $prop)
    {
        $db_con = Constant::connect_to_db('ntu');
        if(empty($db_con))
        {
            return true;
        }
        
        $time_zone = new DateTimeZone('Asia/Singapore');
        $today_obj = new DateTime();
        $today_obj->setTimezone($time_zone);
        $now_time_str = $today_obj->format('H:i');
        
        $appro_index = SchoolTime::getApproTimeIndex($now_time_str);
        
        if(strcmp($prop, "leave") === 0)
        {
            $sql_check = "select * from rs_relief_info where leave_id_ref in (".  implode(',', $leaveIDList).") and ((DATE(schedule_date) > DATE(NOW())) || (DATE(schedule_date) = DATE(NOW()) && start_time_index >= $appro_index));";
        }
        else
        {
            $sql_check = "select * from (rs_temp_relief_teacher_availability left join rs_relief_info on rs_temp_relief_teacher_availability.teacher_id = rs_relief_info.relief_teacher) where rs_temp_relief_teacher_availability.temp_availability_id in (".  implode(',', $leaveIDList).") and ((DATE(rs_relief_info.schedule_date) > DATE(NOW())) || (DATE(rs_relief_info.schedule_date) = DATE(NOW()) && rs_relief_info.start_time_index >= $appro_index));";
        }
        $check_result = Constant::sql_execute($db_con, $sql_check);
        if(is_null($check_result))
        {
            return true;
        }
        if(!empty($check_result))
        {
            //has future relief
            return true;
        }
        
        return false;
    }
    
    public static function delete($leaveIDList, $prop, $has_relief = true)
    {
        if(count($leaveIDList) === 0)
        {
            return false;
        }
        
        $time_zone = new DateTimeZone('Asia/Singapore');
        $today_obj = new DateTime();
        $today_obj->setTimezone($time_zone);
        $now_time_str = $today_obj->format('H:i');
        
        $appro_index = SchoolTime::getApproTimeIndex($now_time_str);
        
        if(strcmp($prop, "leave") === 0)
        {
            $teacher_contact = Teacher::getTeacherContact();
            $AED_list = Teacher::getTeacherInfo("AED");

            $db_con = Constant::connect_to_db('ntu');

            if (empty($db_con))
            {
                return false;
            }

            $sql_all_relief = "select relief_id, relief_teacher from rs_relief_info where leave_id_ref in (".implode(',', $leaveIDList).");";
            $all_relief = Constant::sql_execute($db_con, $sql_all_relief);
            $all_relief_dict = array();
            foreach($all_relief as $row)
            {
                $all_relief_dict[$row["relief_id"]] = array();

                if(array_key_exists($row["relief_teacher"], $AED_list))
                {
                    $all_relief_dict[$row["relief_id"]] = AdHocSchedulerDB::searchSkipForRelief($row["relief_id"]);
                }
            }

            if($has_relief)
            {
                $sql_check = "select relief_id, relief_teacher from rs_relief_info where leave_id_ref in (".implode(',', $leaveIDList).") and ((DATE(schedule_date) > DATE(NOW())) || (DATE(schedule_date) = DATE(NOW()) && start_time_index >= $appro_index));";
                $check_result = Constant::sql_execute($db_con, $sql_check);
                if(is_null($check_result))
                {
                    return false;
                }

                $notified_relief = array();
                foreach($check_result as $row)
                {
                    $notified_relief[] = $row["relief_id"];
                }
                $notified_skip = array();
                foreach($notified_relief as $row)
                {
                    if(array_key_exists($row, $all_relief_dict))
                    {
                        foreach($all_relief_dict[$row] as $one)
                        {
                            $notified_skip[] = $one;
                        }
                    }
                }
            }

            $affected_relief = array_keys($all_relief_dict);
            $affected_skip = array();

            foreach($affected_relief as $row)
            {
                if(array_key_exists($row, $all_relief_dict))
                {
                    foreach($all_relief_dict[$row] as $one)
                    {
                        $affected_skip[] = $one;
                    }
                }
            }

            if(count($notified_relief) > 0 || count($notified_skip) > 0)
            {
                Notification::sendCancelNotification($notified_relief, $notified_skip, $teacher_contact, "");
            }
            
            if(count($affected_skip) > 0)
            {
                //delete skip 
                $sql_delete_skip = "delete from rs_aed_skip_info where skip_id in (".  implode(', ', $affected_skip).");";
                $delete_skip_result = Constant::sql_execute($db_con, $sql_delete_skip);
                if(is_null($delete_skip_result))
                {
                    return false;
                }
            }
            
            //delete leave
            $sql_delete_leave = "delete from rs_leave_info where leave_id in (".  implode(', ', $leaveIDList).");";
            $delete_leave_result = Constant::sql_execute($db_con, $sql_delete_leave);
            if(is_null($delete_leave_result))
            {
                return false;
            }

            return true;
        }
        else if(strcmp($prop, "temp") === 0)
        {
            $teacher_contact = Teacher::getTeacherContact();
            $AED_list = Teacher::getTeacherInfo("AED");

            $db_con = Constant::connect_to_db('ntu');

            if (empty($db_con))
            {
                return false;
            }
            
            $sql_all_relief = "select * from rs_temp_relief_teacher_availability, rs_relief_info where rs_temp_relief_teacher_availability.teacher_id = rs_relief_info.relief_teacher and rs_temp_relief_teacher_availability.temp_availability_id in (".implode(',', $leaveIDList).");";
            $all_relief = Constant::sql_execute($db_con, $sql_all_relief);
            $all_relief_dict = array();
            $affected_relief = array();
            foreach($all_relief as $row)
            {
                $all_relief_dict[$row["relief_id"]] = array(
                    "date" => $row["schedule_date"],
                    "leave_id_ref" => $row["leave_id_ref"],
                    "skip" => array()
                );

                if(array_key_exists($row["relief_teacher"], $AED_list))
                {
                    $all_relief_dict[$row["relief_id"]]["skip"] = AdHocSchedulerDB::searchSkipForRelief($row["relief_id"]);
                }
                
                $affected_relief[] = $row["relief_id"];
            }

            if($has_relief)
            {
                $sql_check = "select * from rs_temp_relief_teacher_availability, rs_relief_info where rs_temp_relief_teacher_availability.teacher_id = rs_relief_info.relief_teacher and rs_temp_relief_teacher_availability.temp_availability_id in (".  implode(',', $leaveIDList).") and ((DATE(rs_relief_info.schedule_date) > DATE(NOW())) || (DATE(rs_relief_info.schedule_date) = DATE(NOW()) && rs_relief_info.start_time_index >= $appro_index));";
                $check_result = Constant::sql_execute($db_con, $sql_check);
                if(is_null($check_result))
                {
                    return false;
                }

                $notified_relief = array();
                foreach($check_result as $row)
                {
                    $notified_relief[] = $row["relief_id"];
                }
                $notified_skip = array();
                foreach($notified_relief as $row)
                {
                    if(array_key_exists($row, $all_relief_dict))
                    {
                        foreach($all_relief_dict[$row]["skip"] as $one)
                        {
                            $notified_skip[] = $one;
                        }
                    }
                }
            }

            $affected_skip = array();
            $affected_leave = array();

            foreach($affected_relief as $row)
            {
                foreach($all_relief_dict[$row]["skip"] as $one)
                {
                    $affected_skip[] = $one;
                }

                $affected_leave[]  = array($all_relief_dict[$row]['leave_id_ref'], $all_relief_dict[$row]["date"]);
            }
            
            if(count($notified_relief) > 0 || count($notified_skip) > 0)
            {
                Notification::sendCancelNotification($notified_relief, $notified_skip, $teacher_contact, "");
            }
            
            if(count($affected_leave) > 0)
            {
                //delete is scheduled
                $sql_delete_leave_scheduled = "delete from rs_leave_scheduled where ";
                foreach($affected_leave as $a_leave)
                {
                    $sql_delete_leave_scheduled .= "(leave_id = $a_leave[0] and schedule_date = DATE('$a_leave[1]')) or ";
                }
                $sql_delete_leave_scheduled = substr($sql_delete_leave_scheduled, 0, -4).';';
                $delete_scheduled = Constant::sql_execute($db_con, $sql_delete_leave_scheduled);
                if(is_null($delete_scheduled))
                {
                    throw new DBException('Fail to delete relief', __FILE__, __LINE__);
                }
            }

            if(count($affected_relief) > 0)
            {
                //delete relief
                $delete_list = implode(',', $affected_relief);
                $sql_delete_id = "delete from rs_relief_info where relief_id in ($delete_list);";
                $delete_id = Constant::sql_execute($db_con, $sql_delete_id);
                if(is_null($delete_id))
                {
                    throw new DBException('Fail to delete relief', __FILE__, __LINE__);
                }
            }

            if(count($affected_skip) > 0)
            {
                //delete skip 
                $sql_delete_skip = "delete from rs_aed_skip_info where skip_id in (".  implode(', ', $affected_skip).");";
                $delete_skip_result = Constant::sql_execute($db_con, $sql_delete_skip);
                if(is_null($delete_skip_result))
                {
                    return false;
                }
            }
            
            $sql_delete_temp = "delete from rs_temp_relief_teacher_availability where temp_availability_id in (".  implode(', ', $leaveIDList).");";
            $delete_temp_result = Constant::sql_execute($db_con, $sql_delete_temp);
            if(is_null($delete_temp_result))
            {
                return false;
            }

            return true;
        }
        else
        {
            return false;
        }
    }

    public static function edit($leaveID, $prop, $change, $has_relief)
    {
        $db_con = Constant::connect_to_db('ntu');

        if(empty($db_con))
        {
            return false;
        }

        if(empty($change) || count($change)===0)
        {
            return false;
        }

        if(strcmp($prop, "leave") === 0)
        {
            if(empty($change['datetime-from']) && empty($change['datetime-to']))
            {
                 $match_array = Array(
                    'reason' => 'reason',
                    'remark' => 'remark',
                );
                
                $sql_update_leave = "update rs_leave_info set ";
                $has_change = false;
                
                foreach($change as $key => $value)
                {
                    if(array_key_exists($key, $match_array))
                    {
                        $has_change = true;
                        $sql_update_leave .= $match_array[$key]."='".mysql_real_escape_string($value)."',";
                    }
                }
                
                if($has_change)
                {
                    $sql_update_leave = substr($sql_update_leave, 0 ,-1)." ";
                    $sql_update_leave .= "where leave_id = ".$leaveID.";";

                    $update_leave_result = Constant::sql_execute($db_con, $sql_update_leave);

                    if(!$update_leave_result)
                    {
                        return false;
                    }
                }
                
                return true;
            }
            else
            {
                $sql_query = "select * from rs_leave_info where leave_id = $leaveID;";
                $query_result = Constant::sql_execute($db_con, $sql_query);
                if(is_null($query_result))
                {
                    return false;
                }

                $row = $query_result[0];

                $reason = empty($change['reason'])?$row['reason']:$change['reason'];
                $remark = empty($change['remark'])?$row['remark']:$change['remark'];
                $datetim_from = empty($change['datetime-from'])?$row['start_time']:$change['datetime-from'];
                $datetim_to = empty($change['datetime-to'])?$row['end_time']:$change['datetime-to'];

                if(!Teacher::delete(array($leaveID), "leave", $has_relief))
                {
                    return false;
                }
                return Teacher::add($row['teacher_id'], "leave", array("datetime-from" => $datetim_from, "datetime-to" => $datetim_to, "reason" => $reason, "remark" => $remark), false ,$leaveID);
            }
        }
        else if(strcmp($prop, "temp") === 0)
        {
            $teacher_match_array = Array(
                'handphone' => 'mobile',
                'email' => 'email',
                'MT' => 'mother_tongue'
            );
            $temp_match_array = Array(
                'remark' => 'slot_remark'
                //'datetime-from' => 'start_datetime',
                //'datetime-to' => 'end_datetime'
            );

            $sql_update_teacher = "update rs_temp_relief_teacher set ";
            $sql_update_temp = "update rs_temp_relief_teacher_availability set ";

            $teacher_change = false;
            $remark_change = false;

            foreach($change as $key => $value)
            {
                if(array_key_exists($key, $teacher_match_array))
                {
                    $teacher_change = true;
                    $sql_update_teacher .= $teacher_match_array[$key]."='".mysql_real_escape_string(trim($value))."',";
                }
                else if(array_key_exists($key, $temp_match_array))
                {
                    $remark_change = true;
                    $sql_update_temp .= $temp_match_array[$key]."='".mysql_real_escape_string(trim($value))."',";
                }
            }

            $sql_update_temp = substr($sql_update_temp, 0 ,-1)." ";
            $sql_update_temp .= "where temp_availability_id = ".mysql_real_escape_string(trim($leaveID)).";";

            //query teacher id
            $sql_get_teacher_id = "select * from rs_temp_relief_teacher_availability where temp_availability_id = ".mysql_real_escape_string(trim($leaveID)).";";
            $get_teacher_id_result = Constant::sql_execute($db_con, $sql_get_teacher_id);
            if(is_null($get_teacher_id_result))
            {
                return false;
            }
            $row = $get_teacher_id_result[0];
            if(!$row)
            {
                return false;
            }
            else
            {
                $teacher_id = $row['teacher_id'];
                $remark = $row['slot_remark'];
            }
            
            if(empty($change['datetime-from']) && empty($change['datetime-to']))
            {
                if($teacher_change)
                {
                    $sql_update_teacher = substr($sql_update_teacher, 0 ,-1)." ";
                    $sql_update_teacher .= "where teacher_id = '".$teacher_id."';";

                    $update_teacher_result = Constant::sql_execute($db_con, $sql_update_teacher);

                    if(!$update_teacher_result)
                    {throw new DBException($sql_get_teacher_id, __FILE__, __LINE__);
                        return false;
                    }
                }
                
                if($remark_change)
                {
                    $update_temp_result = Constant::sql_execute($db_con, $sql_update_temp);

                    if(!$update_temp_result)
                    {throw new DBException($sql_get_teacher_id, __FILE__, __LINE__);
                        return false;
                    }
                }
            }
            else
            {
                $sql_old_teacher = "select * from rs_temp_relief_teacher where teacher_id = '$teacher_id';";
                $old_teacher = Constant::sql_execute($db_con, $sql_old_teacher);
                if(empty($old_teacher))
                {
                    return false;
                }
                
                $row_old = $old_teacher[0];
                
                $name = empty($change['fullname'])?$row_old['name']:$change['fullname'];
                $email = empty($change['email'])?$row_old['email']:$change['email'];
                $MT = empty($change['MT'])?$row_old['mother_tongue']:$change['MT'];
                $phone = empty($change['handphone'])?$row_old['mobile']:$change['handphone'];
                $remark = empty($change['remark'])?$remark:$change['remark'];
                $datetime_from_temp = empty($change['datetime-from'])?$row['start_datetime']:$change['datetime-from'];
                $datetime_to_temp = empty($change['datetime-to'])?$row['end_datetime']:$change['datetime-to'];
                
                /*
                if(!Teacher::delete(array($leaveID), "temp", $has_relief))
                {
                    return false;
                }
                 * 
                 */
                //delete old teacher
                /*
                $sql_delete_teacher = "delete from rs_temp_relief_teacher where teacher_id = '$teacher_id';";
                $delete_teacher = Constant::sql_execute($db_con, $sql_delete_teacher);
                if(is_null($delete_teacher))
                {
                    return false;
                }
                 * 
                 */
                if(!Teacher::delete(array($leaveID), "temp", $has_relief))
                {
                    return false;
                }

                if(Teacher::add($teacher_id, "temp", array("remark" => $remark, "datetime-from" => $datetime_from_temp, "datetime-to" => $datetime_to_temp, "MT" => $MT, "handphone" => $phone, "email" => $email, "fullname" => $name), $has_relief, $leaveID) < 0)
                {
                    return false;
                }
                else
                {
                    return true;
                }
            }
            
            return true;
        }
    }
    
    /**
     * For prop=leave, only accname, reason, remark, datetime-from, datetime-to can be updated;
     * For prop=temp, only datetime-from, datetime-to, remark, phone, email, MT can be updated;
     * @param type $leaveID
     * @param type $prop
     * @param type $change
     * @return boolean
     */
    private static function edit_v2($leaveID, $prop, $change)
    {
        $db_con = Constant::connect_to_db('ntu');

        if(empty($db_con))
        {
            return false;
        }

        if(empty($change) || count($change)===0)
        {
            return false;
        }

        if(strcmp($prop, "leave") === 0)
        {
            $match_array = Array(
                'accname' => 'teacher_id',
                'reason' => 'reason',
                'remark' => 'remark',
                'datetime-from' => 'start_time',
                'datetime-to' => 'end_time'
            );

            $sql_update_leave = "update rs_leave_info set ";

            foreach($change as $key => $value)
            {
                if(array_key_exists($key, $match_array))
                {
                    $sql_update_leave .= $match_array[$key]."='".mysql_real_escape_string($value)."',";
                }
            }

            if(array_key_exists("datetime-from", $change) || array_key_exists("datetime-to", $change))
            {
                $sql_query_leave = "select teacher_id, DATE_FORMAT(start_time, '%Y-%m-%d %H:%i') as start, DATE_FORMAT(end_time, '%Y-%m-%d %H:%i') as end from rs_leave_info where leave_id = ".$leaveID.";";
                $query_leave_result = Constant::sql_execute($db_con, $sql_query_leave);
                if(is_null($query_leave_result))
                {
                    return false;
                }

                $row = $query_leave_result[0];
                if(!$row)
                {
                    return false;
                }

                if(array_key_exists("datetime-from", $change) && !array_key_exists("datetime-to", $change) )
                {
                    $num_of_slot = Teacher::calculateLeaveSlot($row['teacher_id'], $change['datetime-from'], $row['end']);
                }
                else if(!array_key_exists("datetime-from", $change) && array_key_exists("datetime-to", $change) )
                {
                    $num_of_slot = Teacher::calculateLeaveSlot($row['teacher_id'], $row['start'], $change['datetime-to']);
                }
                else
                {
                    $num_of_slot = Teacher::calculateLeaveSlot($row['teacher_id'], $change['datetime-from'], $change['datetime-to']);
                }

                $sql_update_leave .= "num_of_slot=".$num_of_slot.",";
            }

            $sql_update_leave = substr($sql_update_leave, 0 ,-1)." ";
            $sql_update_leave .= "where leave_id = ".$leaveID.";";

            $update_leave_result = Constant::sql_execute($db_con, $sql_update_leave);

            if(!$update_leave_result)
            {
                return false;
            }

            return true;
        }
        else if(strcmp($prop, "temp") === 0)
        {
            $teacher_match_array = Array(
                'handphone' => 'mobile',
                'email' => 'email',
                'MT' => 'mother_tongue'
            );
            $temp_match_array = Array(
                'remark' => 'slot_remark',
                'datetime-from' => 'start_datetime',
                'datetime-to' => 'end_datetime'
            );

            $sql_update_teacher = "update rs_temp_relief_teacher set ";
            $sql_update_temp = "update rs_temp_relief_teacher_availability set ";

            $teacher_change = false;
            $temp_change = false;

            foreach($change as $key => $value)
            {
                if(array_key_exists($key, $teacher_match_array))
                {
                    $teacher_change = true;
                    $sql_update_teacher .= $teacher_match_array[$key]."='".mysql_real_escape_string(trim($value))."',";
                }
                else if(array_key_exists($key, $temp_match_array))
                {
                    $temp_change = true;
                    $sql_update_temp .= $temp_match_array[$key]."='".mysql_real_escape_string(trim($value))."',";
                }
            }

            $sql_update_temp = substr($sql_update_temp, 0 ,-1)." ";
            $sql_update_temp .= "where temp_availability_id = ".mysql_real_escape_string(trim($leaveID)).";";

            if($teacher_change)
            {
                $sql_get_teacher_id = "select teacher_id from rs_temp_relief_teacher_availability where temp_availability_id = ".mysql_real_escape_string(trim($leaveID)).";";
                $get_teacher_id_result = Constant::sql_execute($db_con, $sql_get_teacher_id);
                if(is_null($get_teacher_id_result))
                {
                    return false;
                }
                $row = $get_teacher_id_result[0];
                if(!$row)
                {
                    return false;
                }
                else
                {
                    $teacher_id = $row['teacher_id'];
                }

                $sql_update_teacher = substr($sql_update_teacher, 0 ,-1)." ";
                $sql_update_teacher .= "where teacher_id = '".$teacher_id."';";

                $update_teacher_result = Constant::sql_execute($db_con, $sql_update_teacher);

                if(!$update_teacher_result)
                {
                    return false;
                }
            }
            if($temp_change)
            {
                $update_temp_result = Constant::sql_execute($db_con, $sql_update_temp);

                if(!$update_temp_result)
                {
                    return false;
                }
            }

            return true;
        }
    }

    /**
     * return all teachers in ifins
     * @return array of teachers asso array. key: name, type, mobile
     */
    public static function  getAllTeachers()
    {
        $teacher_dict = Array();

        $ifins_db_con = Constant::connect_to_db("ifins");

        if (empty($ifins_db_con))
        {
            throw new DBException("Fail to connect to database", __FILE__, __LINE__);
        }

        $sql_query_teacher = "select user_id, user_name, dept_name, user_mobile from student_details where user_position = 'Teacher';";
        $result_teacher = Constant::sql_execute($ifins_db_con, $sql_query_teacher);
        if(is_null($result_teacher))
        {
            throw new DBException("Fail to query teacher from database", __FILE__, __LINE__, 2);
        }

        foreach($result_teacher as $row)
        {
            $teacher_dict[$row['user_id']] = Array(
                'name' => $row['user_name'],
                'type' => $row['dept_name'],
                'mobile' => $row['user_mobile']
            );
        }

        return $teacher_dict;
    }

    public static function insertAbbrMatch($all_matches)
    {
        if(count($all_matches) === 0)
        {
            return 0;
        }
        
        $abbre_dict = Teacher::getAbbreMatch();
        
        $db_con = Constant::connect_to_db("ntu");

        if(empty($db_con))
        {
            return 0;
        }

        $have_exist = false;
        $sql_delete_exist = "delete from ct_name_abbre_matching where teacher_id in (";
        $sql_insert_match = "insert into ct_name_abbre_matching values ";

        $acc_list = array();
        
        foreach($all_matches as $abbre=>$accname)
        {
            if(in_array($accname, $acc_list))
            {
                return -1;
            }
            
            if(array_key_exists($accname, $abbre_dict))
            {
                $have_exist = true;
                $sql_delete_exist .= "'".$accname."',";
            }

            $sql_insert_match .= "('".$accname."', '".$abbre."'),";
            $acc_list[] = $accname;
        }

        if($have_exist)
        {
            $sql_delete_exist = substr($sql_delete_exist, 0, -1).');';
            
            $delete_exist_result = Constant::sql_execute($db_con, $sql_delete_exist);
            if(is_null($delete_exist_result))
            {
                return 0;
            }
        }

        $sql_insert_match = substr($sql_insert_match, 0, -1).';';       
        $insert_result = Constant::sql_execute($db_con, $sql_insert_match);
        if(is_null($insert_result))
        {
            return 0;
        }

        return 1;
    }

    /**
     * 
     * @param type $type
     * @param type $order
     * @param type $direction
     * @param type $year
     * @param type $sem
     * @param boolean $future_leave true -> count in future leave, false -> ignore future leave
     * @return type
     * @throws DBException
     */
    public static function overallReport($type = "", $order = "fullname", $direction = SORT_ASC, $year = "2013", $sem = 1, $future_leave = false)
    {
        $result = array();

        $db_con = Constant::connect_to_db("ntu");
        if(empty($db_con))
        {
            throw new DBException('Fail to query report', __FILE__, __LINE__);
        }

        $mc_dic = array();
        
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
                    $mc_dic[$accname] = 0;
                }
                
                $end_date_str = $row['end_datetime'];
                $end_date = new DateTime($end_date_str);
                $end_date_stamp = $end_date->getTimestamp();
                
                if($end_date_stamp > $today_stamp)
                {
                    //need to trim
                    $start_date_str = $row['start_datetime'];
                    $trimed_slot = Teacher::calculateLeaveSlot($accname, $start_date_str, $end_date_str);
                    
                    $mc_dic[$accname] += $trimed_slot;
                }
                else
                {
                    //no future leave to trim
                    $mc_dic[$accname] += $row["num_of_slot"];
                }
            }
        }
        
        $relief_dic = array();
        if($future_leave)
        {
            $sql_query_relief = "select relief_teacher, sum(num_of_slot) as num_of_relief from (select rs_relief_info.* from rs_relief_info, ct_semester_info where ct_semester_info.year = '$year' and ct_semester_info.sem_num = $sem and (DATE(rs_relief_info.schedule_date) between ct_semester_info.start_date and ct_semester_info.end_date)) AS temp_relief group by relief_teacher;";
        }
        else
        {
            $sql_query_relief = "select relief_teacher, sum(num_of_slot) as num_of_relief from (select rs_relief_info.* from rs_relief_info, ct_semester_info where ct_semester_info.year = '$year' and ct_semester_info.sem_num = $sem and (DATE(rs_relief_info.schedule_date) between ct_semester_info.start_date and ct_semester_info.end_date) and DATE(rs_relief_info.schedule_date) < DATE(NOW())) AS temp_relief group by relief_teacher;";
        }
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
                $a_record['numOfMC'] = $mc_dic[$a_record['accname']];
            }
            else
            {
                $a_record['numOfMC'] = 0;
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

            $result[] =$a_record;
        }

        //sort
        $sort_arr = Array();

        foreach($result as $key=>$value)
        {
            $sort_arr[$key] = $value[$order];
        }

        array_multisort($sort_arr, $direction, $result);

        return $result;
    }

    public static function individualReport($accname, $year = '2013', $sem = 1, $future_leave = false)
    {
        $result = Array(
            "numOfMC" => 0,
            "numOfRelief" => 0,
            "mc" => Array(),
            "relief" => Array()
        );

        $db_con = Constant::connect_to_db("ntu");
        if(empty($db_con))
        {
            throw new DBException('Fail to query individual report', __FILE__, __LINE__);
        }

        //leave
        $sql_query_leave = "select *, DATE_FORMAT(start_time, '%Y/%m/%d') as start_date_point, DATE_FORMAT(end_time, '%Y/%m/%d') as end_date_point, TIME_FORMAT(start_time, '%H:%i') as start_time_point, TIME_FORMAT(end_time, '%H:%i') as end_time_point from rs_leave_info, ct_semester_info where teacher_id = '".mysql_real_escape_string(trim($accname))."' and ct_semester_info.year = '$year' and ct_semester_info.sem_num = $sem and (DATE(rs_leave_info.start_time) between ct_semester_info.start_date and ct_semester_info.end_date);";

        $query_leave_result = Constant::sql_execute($db_con, $sql_query_leave);
        if(is_null($query_leave_result))
        {
            throw new DBException('Fail to query individual report', __FILE__, __LINE__, 2);
        }

        if($future_leave)
        {
            foreach($query_leave_result as $row)
            {
                $result['numOfMC'] += $row['num_of_slot'] - 0;

                $one_leave = Array(Array($row['start_date_point'], $row['start_time_point']), Array($row['end_date_point'], $row['end_time_point']));
                $result['mc'][] = $one_leave;
            }
        }
        else
        {
            $today = new DateTime();
            $asia_timezone = new DateTimeZone("Asia/Singapore");
            $today->setTimezone($asia_timezone);
            $today_stamp = $today->getTimestamp();
            
            foreach($query_leave_result as $row)
            {
                $start_datetime_str = $row['start_date_point']." ".$row['start_time_point'];
                $start_date = new DateTime($start_datetime_str);
                $start_stamp = $start_date->getTimestamp();
                
                if($start_stamp > $today_stamp)
                {
                    continue;
                }
                
                $end_datetime_str = $row['end_date_point']." ".$row['end_time_point'];
                $end_date = new DateTime($end_datetime_str);
                $end_stamp = $end_date->getTimestamp();
                
                if($end_stamp > $today_stamp)
                {
                    $trimed_slot = Teacher::calculateLeaveSlot($accname, $start_datetime_str, $end_datetime_str);
                    $result['numOfMC'] += $trimed_slot;
                    $one_leave = Array(Array($row['start_date_point'], $row['start_time_point']), Array($today->format("Y/m/d"), $today->format("H:i")));
                }
                else
                {
                    $one_leave = Array(Array($row['start_date_point'], $row['start_time_point']), Array($row['end_date_point'], $row['end_time_point']));
                    $result['numOfMC'] += $row['num_of_slot'] - 0;
                }
                
                $result['mc'][] = $one_leave;
            }
        }
        
        //relief
        if($future_leave)
        {
            $sql_query_relief = "select *, DATE_FORMAT(schedule_date, '%Y/%m/%d') as date from rs_relief_info, ct_semester_info where relief_teacher = '".mysql_real_escape_string(trim($accname))."' and ct_semester_info.year = '$year' and ct_semester_info.sem_num = $sem and (DATE(rs_relief_info.schedule_date) between ct_semester_info.start_date and ct_semester_info.end_date);";
        }
        else
        {
            $sql_query_relief = "select *, DATE_FORMAT(schedule_date, '%Y/%m/%d') as date from rs_relief_info, ct_semester_info where relief_teacher = '".mysql_real_escape_string(trim($accname))."' and ct_semester_info.year = '$year' and ct_semester_info.sem_num = $sem and (DATE(rs_relief_info.schedule_date) between ct_semester_info.start_date and ct_semester_info.end_date) and DATE(rs_relief_info.schedule_date) < DATE(NOW());";
        }
        $query_relief_result = Constant::sql_execute($db_con, $sql_query_relief);
        if(is_null($query_relief_result))
        {
            throw new DBException('Fail to query individual report', __FILE__, __LINE__, 2);
        }

        foreach($query_relief_result as $row)
        {
            $result['numOfRelief'] += $row['num_of_slot'] - 0;

            $one_relief = Array(Array($row['date'], SchoolTime::getTimeValue($row['start_time_index'])), Array($row['date'], SchoolTime::getTimeValue($row['end_time_index'])));

            $result['relief'][] = $one_relief;
        }

        return $result;
    }

    /**
     * return all teacher's contact
     * @return array {"acc1" => {"phone"=>xxx, "email"=>xxx}}
     */
    public static function getTeacherContact()
    {
        $result = Array();
        
        //normal teacher
        $db_con_ifins = Constant::connect_to_db("ifins");
        if(empty($db_con_ifins))
        {
            throw new DBException("Fail to get teachers' contact", __FILE__, __LINE__);
        }
        
        $sql_ifins_contact = "select user_id, user_name, user_mobile, user_email from student_details where user_position = 'Teacher';";
        $ifins_result = Constant::sql_execute($db_con_ifins, $sql_ifins_contact);
        if(is_null($ifins_result))
        {
            throw new DBException("Fail to get teachers' contact", __FILE__, __LINE__);
        }
        
        foreach($ifins_result as $row)
        {
            $phone = empty($row['user_mobile'])?"":$row['user_mobile'];
            $email = empty($row['user_email'])?"":$row['user_email'];
            $name = empty($row['user_name'])?"":$row['user_name'];
            
            $result[$row['user_id']] = Array(
                "phone" => $phone,
                "email" => $email,
                "name" => $name
            );
        }
        
        //temp teacher
        $db_con = Constant::connect_to_db("ntu");
        if(empty($db_con))
        {
            throw new DBException("Fail to get teachers' contact", __FILE__, __LINE__);
        }
        
        $sql_ntu_contact = "select teacher_id, name, mobile, email from rs_temp_relief_teacher;";
        $ntu_result = Constant::sql_execute($db_con, $sql_ntu_contact);
        if(is_null($ntu_result))
        {
            throw new DBException("Fail to get teachers' contact", __FILE__, __LINE__);
        }
        
        foreach($ntu_result as $row)
        {
            $phone = empty($row['mobile'])?"":$row['mobile'];
            $email = empty($row['email'])?"":$row['email'];
            $name = empty($row['name'])?"":$row['name'];
            
            $result[$row['teacher_id']] = Array(
                "phone" => $phone,
                "email" => $email,
                "name" => $name
            );
        }
        
        return $result;
    }
    
    private static function getAbbreMatch()
    {
        $result = Array();

        $db_con = Constant::connect_to_db("ntu");

        if(empty($db_con))
        {
            throw new DBException('Fail to query name abbre match', __FILE__, __LINE__);
        }

        $sql_query_match = "select * from ct_name_abbre_matching;";

        $query_match_result = Constant::sql_execute($db_con, $sql_query_match);
        if(is_null($query_match_result))
        {
            throw new DBException('Fail to query name abbre match', __FILE__, __LINE__, 2);
        }

        foreach($query_match_result as $row)
        {
            $result[$row['teacher_id']] = $row['abbre_name'];
        }

        return $result;
    }

    /**
     *
     * @param string $datetime_from
     * @param string $datetime_to
     * @return int number of time slot in between, excludig holidays; -1 if error
     */
    public static function calculateLeaveSlot($teacher_id, $datetime_from, $datetime_to)
    {
        $datetime_from_arr = explode(" ", trim($datetime_from));
        $datetime_to_arr = explode(" ", trim($datetime_to));

        $start_date = new DateTime($datetime_from_arr[0]);
        $end_date = new DateTime($datetime_to_arr[0]);

        $readable_time_from = $datetime_from_arr[1];
        $readable_time_to = $datetime_to_arr[1];
        $first_index = 1;
        $last_index = 15;

        $start_time_index = SchoolTime::getTimeIndex($readable_time_from);
        $end_time_index = SchoolTime::getTimeIndex($readable_time_to);
        
        if($start_time_index === -1)
        {
            $start_time_index = $first_index;
        }
        if($end_time_index === -1)
        {
            $end_time_index = $last_index;
        }
        
        $interval = $end_date->diff($start_date);

        $num_of_slot = 0;
        $slot_dict = Teacher::getLessonSlotsOfTeacher($teacher_id);
        
        $start_end = Array();

        //put start and end time of each day into $start_end
        if($interval->d<0)
        {
            return -1;
        }
        else if($interval->d===0)
        {
            $weekday = $start_date->format('N');
            if($weekday === '6' || $weekday === '7')
            {
                return 0;
            }

            $start_end[$weekday-0] = Array($start_time_index, $end_time_index, 1);
        }
        else if($interval->d===1)
        {
            $weekday_start = $start_date->format('N');
            if($weekday_start !== '6' && $weekday_start !== '7')
            {
                $start_end[$weekday_start-0] = Array($start_time_index, $last_index, 1);
            }
            $weekday_end = $end_date->format('N');
            if($weekday_end !== '6' && $weekday_end !== '7')
            {
                $start_end[$weekday_end-0] = Array($first_index, $end_time_index, 1);
            }
        }
        else
        {
            $day_interval = new DateInterval('P0Y0M1DT0H0M0S');

            $weekday_start = $start_date->format('N');
            if($weekday_start !== '6' && $weekday_start !== '7')
            {
                $start_end[8] = Array($start_time_index, $last_index, 1, $weekday_start-0);
            }
            $weekday_end = $end_date->format('N');
            if($weekday_end !== '6' && $weekday_end !== '7')
            {
                $start_end[9] = Array($first_index, $end_time_index, 1, $weekday_end-0);
            }

            $begin_loop = $start_date->add($day_interval);
            while($begin_loop->diff($end_date, true)->d>0)
            {
                $weekday_loop = $begin_loop->format('N');
                if($weekday_loop !== '6' && $weekday_loop !== '7')
                {
                    if(empty($start_end[$weekday_loop-0]))
                    {
                        $start_end[$weekday_loop-0] = Array($first_index, $last_index, 1);
                    }
                    else
                    {
                        $start_end[$weekday_loop-0][2]++;
                    }
                }

                $begin_loop = $begin_loop->add($day_interval);
            }
        }

        //calculate num of slots
        foreach($start_end as $key=>$value)
        {
            //line 797, 802, start and end date of a leave is handled separately
            if($key===8 || $key===9)
            {
                $slots = $slot_dict[$value[3]];
            }
            else
            {
                $slots = $slot_dict[$key];
            }

            $weekday_num=0;

            foreach($slots as $a_slot)
            {
                if($value[0]>=$a_slot[1])
                {
                    continue;
                }
                if($value[1]<=$a_slot[0])
                {
                    continue;
                }
                if($a_slot[0]<$value[0] && $a_slot[1]>$value[1])
                {
                    $weekday_num+=$value[1]-$value[0];
                    break;
                }
                if($value[0]<$a_slot[1] && $value[0]>$a_slot[0])
                {
                    //assumption: when a lesson span over multiple time slots, teacher on leave will take the time slots lessons outside the leave time duration
                    $weekday_num+=$a_slot[1]-$value[0];
                    continue;
                }
                if($value[1]<$a_slot[1] && $value[1]>$a_slot[0])
                {
                    //assumption: when a lesson span over multiple time slots, teacher on leave will take the time slots lessons outside the leave time duration
                    $weekday_num+=$value[1]-$a_slot[0];
                    continue;
                }

                $weekday_num+=$a_slot[1]-$a_slot[0];
            }
            $weekday_num *= $value[2];
            $num_of_slot += $weekday_num;
        }

        return $num_of_slot;
    }

    private static function getLessonSlotsOfTeacher($teacher_id)
    {
        //each weekday has one array
        $result = Array(
            1 => Array(),
            2 => Array(),
            3 => Array(),
            4 => Array(),
            5 => Array()
        );

        $db_con = Constant::connect_to_db('ntu');
        
        if (empty($db_con))
        {
            throw new DBException('Fail to query lesson slot for teacher', __FILE__, __LINE__);
        }

        $sql_query_time_slot = "select distinct weekday, start_time_index, end_time_index from ct_lesson, ct_teacher_matching where ct_lesson.lesson_id = ct_teacher_matching.lesson_id and ct_teacher_matching.teacher_id='".mysql_real_escape_string($teacher_id)."';";
        $query_time_slot_result = Constant::sql_execute($db_con, $sql_query_time_slot);
        if(is_null($query_time_slot_result))
        {
            throw new DBException('Fail to query lesson slot for teacher', __FILE__, __LINE__, 2);
        }

        foreach($query_time_slot_result as $row)
        {
            $result[$row['weekday']][] = Array($row['start_time_index'], $row['end_time_index']);
        }

        return $result;
    }
}

?>
