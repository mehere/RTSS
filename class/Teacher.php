<?php
require_once 'util.php';

class Teacher {

    //put your code here
    public $abbreviation;
    public $timetable;
    public $name;
    public $accname;

    public function __construct($abbreviation) {
        $this->abbreviation = $abbreviation;
        $this->name = NULL;
        $this->accname = NULL;
        $this->timetable = array();
    }
    
    //this function finds full name and accname for a list of teachers, given abbre name
    //input : Array of teachers, with abbre nave known
    //output : Array of teachers, with name and accname returned
    //error : An empty array is returned
    public static function getTeachersAccnameAndFullname($teacher_list)
    {
        $result_list = Array();
        
        //get abbre-accname list
        $db_url = Constant::db_url;
        $db_username = Constant::db_username;
        $db_password = Constant::db_password;
        $db_name = Constant::db_name;
        
        $db_con = mysql_connect($db_url, $db_username, $db_password);
        
        if (!$db_con)
        {
            return $result_list;
        }
        
        mysql_select_db($db_name, $db_con);
        
        $sql_query = "select * from ct_name_abbre_matching;";
        $result = mysql_query($sql_query);
        if(!$result)
        {
            return $result_list;
        }
        
        $abbre_dict = Array();
        while($row = mysql_fetch_array($result))
        {
            $abbre_dict[str_replace(" ", "_", $row['abbre_name'])] = $row['teacher_id'];
        }
        
        //get accname - teacher list
        $teacher_dict = Teacher::getAllTeachers();
        
        //search teacher name
        foreach($teacher_list as $key => $a_teacher)
        {
            $temp_teacher = new Teacher($a_teacher->abbreviation);
            
            if(!empty($abbre_dict[str_replace(" ", "_", $a_teacher->abbreviation)]))
            {
                $temp_teacher->accname=$abbre_dict[str_replace(" ", "_", $a_teacher->abbreviation)];
                $temp_teacher->name=$teacher_dict[$temp_teacher->accname]['name'];
            }
            
            $result_list[$key] = $temp_teacher;
        }
        
        return $result_list;
    }
    
    //this functio returns all teachers on leave today
    //input : date string, in format 2012-12-11
    //output : array of associative arrays each representing a piece of leave info that's on the input date. Empty - possibly there are errors. Check database for confirmation.
    public static function getTeacherOnLeave($query_date)
    {
        $result = Array();
        
        //query teacher dict
        $teacher_dict = Teacher::getAllTeachers();
        
        //check input
        if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $query_date))
        {
            return $result;
        }
        
        $db_url = Constant::db_url;
        $db_username = Constant::db_username;
        $db_password = Constant::db_password;
        $db_name = Constant::db_name;
        
        $db_con = mysql_connect($db_url, $db_username, $db_password);
        
        if (!$db_con)
        {
            return $result;
        }
        
        mysql_select_db($db_name);
        
        //start
        //query relief info to check whether scheduled
        //No need now
        /*
        $sql_query_relief = "select * from rs_relief_info where date = '".mysql_real_escape_string($query_date)."';";
        $relief_query_result = mysql_query($sql_query_relief);
        if(!$relief_query_result)
        {
            return $result;
        }
        
        $leave_id_array = Array();
        
        while($row = mysql_fetch_assoc($relief_query_result))
        {
            $one_leave_id = $row['leave_id_ref'];
            if(!in_array($one_leave_id, $leave_id_array))
            {
                array_push($leave_id_array, $one_leave_id);
            }
        }
         * 
         */
        
        //query leave
        $sql_query_leave = "select *, DATE(rs_leave_info.start_time) as start_date, DATE(rs_leave_info.end_time) as end_date, TIME_FORMAT(rs_leave_info.start_time, '%H:%i') as start_time_point, TIME_FORMAT(rs_leave_info.end_time, '%H:%i') as end_time_point from rs_leave_info 
            where '".mysql_real_escape_string($query_date)."' between date(rs_leave_info.start_time) and date(rs_leave_info.end_time);";
        
        $query_leave_result = mysql_query($sql_query_leave);
        
        if(!$query_leave_result)
        {
            return $result;
        }
       
        while($row = mysql_fetch_assoc($query_leave_result))
        {
            $each_record = Array();
            $each_record['accname'] = $row['teacher_id'];
            $each_record['reason'] = $row['reason'];
            $each_record['remark'] = empty($row['remark'])?'':$row['remark'];
            $each_record['leaveID'] = $row['leave_id'];
            $each_record['isVerified'] = ($row['verified'] === 'YES')?true:false;
            $each_record['datetime'] = Array(Array($row['start_date'], $row['start_time_point']), Array($row['end_date'], $row['end_time_point']));
            $each_record['isScheduled'] = false;
            /*
            if(in_array($each_record['leaveID'], $leave_id_array))
            {
                $each_record['isScheduled'] = true;
            }
             * 
             */
            
            $each_record['fullname'] = empty($teacher_dict[$row['teacher_id']])?"Teacher not found":$teacher_dict[$row['teacher_id']]['name'];
            $each_record['type'] = empty($teacher_dict[$row['teacher_id']])?"Teacher not found":$teacher_dict[$row['teacher_id']]['type'];
            
            array_push($result, $each_record);
        }
        //end
        
        return $result;
    }
    
    //This function get all temporary teachers
    //input : date string, in format yyyy-mm-dd
    //output : array of associative arrays each representing temporary teacher. MT, remark, email may be ""
    public static function getTempTeacher($query_date)
    {
        $result = Array();
        
        //check input
        if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $query_date))
        {
            return $result;
        }
        
        $db_url = Constant::db_url;
        $db_username = Constant::db_username;
        $db_password = Constant::db_password;
        $db_name = Constant::db_name;
        
        $db_con = mysql_connect($db_url, $db_username, $db_password);
        
        if (!$db_con)
        {
            return $result;
        }
        
        mysql_select_db($db_name);
        
        $sql_query_temp_teacher = "select *, DATE(rs_temp_relief_teacher_availability.start_datetime) as start_date, DATE(rs_temp_relief_teacher_availability.end_datetime) as end_date, TIME_FORMAT(rs_temp_relief_teacher_availability.start_datetime, '%H:%i') as start_time, TIME_FORMAT(rs_temp_relief_teacher_availability.end_datetime, '%H:%i') as end_time 
            from rs_temp_relief_teacher_availability, rs_temp_relief_teacher where rs_temp_relief_teacher_availability.teacher_id=rs_temp_relief_teacher.teacher_id and '".mysql_real_escape_string($query_date)."' between date(rs_temp_relief_teacher_availability.start_datetime) and date(rs_temp_relief_teacher_availability.start_datetime);";
        
        $query_temp_teacher = mysql_query($sql_query_temp_teacher);
        
        if(!$query_temp_teacher)
        {
            return $result;
        }
        
        while($row = mysql_fetch_assoc($query_temp_teacher))
        {
            $one_teacher = Array(
                'remark' => '',
                'MT' => '',
                'email' => '',
                'datetime' => Array()
            );
            
            $one_teacher['accname'] = $row['teacher_id'];
            $one_teacher['fullname'] = $row['name'];
            $one_teacher['type'] = "Temporary";
            $one_teacher['datetime'] = Array(Array($row['start_date'], $row['start_time']), Array($row['end_date'], $row['end_time']));
            $one_teacher['remark'] = (empty($row['slot_remark'])?'':$row['slot_remark']);
            $one_teacher['MT'] = (empty($row['mother_tongue'])?'':$row['mother_tongue']);
            $one_teacher['email'] = (empty($row['email'])?'':$row['email']);
            $one_teacher['handphone'] = (empty($row['mobile'])?'':$row['mobile']);
            
            array_push($result, $one_teacher);
        }
        
        return $result;
    }
    
    //this function returns the details of a teacher
    //input : accname - the name used to log in
    //output : associative array of information. Before retrieving any information, check if($output['found']) to see whether the teacher record is found
    /* temporarily disabled, will release later
    public static function getIndividualTeacherDetail($accname)
    {
        $result = Array(
            'found' => false,
            'ID' => $accname,
            'name' => NULL,
            'gender' => NULL,
            'mobile' => NULL,
            'email' => NULL
        );

        $ifins_db_url = Constant::ifins_db_url;
        $ifins_db_username = Constant::ifins_db_username;
        $ifins_db_password = Constant::ifins_db_password;
        $ifins_db_name = Constant::ifins_db_name;
        
        $ifins_db_con = mysql_connect($ifins_db_url, $ifins_db_username, $ifins_db_password);
        
        if (!$ifins_db_con)
        {
            return $result;
        }
        
        mysql_select_db($ifins_db_name);
        
        //with accname, get fullname from ntu.ac_all_teacher
        $sql_query_fullname = "select name from ac_all_teacher where acc_name = '".mysql_real_escape_string($accname)."';";
        $db_query_result = mysql_query($sql_query_fullname);
        if(!$db_query_result)
        {
            return $result;
        }
        
        $row = mysql_fetch_array($db_query_result);
        if($row)
        {
            $result['name'] = $row['name'];
        }
        else
        {
            return $result;
        }
        
        //with full name, query information from ifins_2012.actatek_user
        $sql_query_detail = "select * from actatek_user where user_name = '".mysql_real_escape_string($result['name'])."';";
        $ifins_query_result = mysql_query($sql_query_detail);
        
        if(!$ifins_query_result)
        {
            return $result;
        }
        
        $ifins_row = mysql_fetch_array($ifins_query_result);
        if(!$ifins_row)
        {
            return $result;
        }
       
        $result['found'] = true;
        $result['gender'] = $ifins_row['user_gender'];
        $result['mobile'] = $ifins_row['user_mobile'];
        $result['email'] = $ifins_row['user_email'];
       
        return $result;
    }
     * 
     */
    
    //this function finds a list of alternatives for abbre name of all teachers
    //this function is used when the 1-to-1 match of abbre and full name is not established
    //input : an array of teacher objects, with abbre name provided
    //output : NA
    /* temporarily disabled. not updated
    public static function abbreToFullnameBatchSetup($teacher_list)
    {
        $db_url = Constant::ifins_db_url;
        $db_username = Constant::ifins_db_username;
        $db_password = Constant::ifins_db_password;
        $db_name = Constant::ifins_db_name;
        
        $db_con = mysql_connect($db_url, $db_username, $db_password);
        
        if (!$db_con)
        {
            die("function Teacher::abbreToFullnameBatch : Could not connect to database");
        }
        
        mysql_select_db($db_name);
        
        foreach($teacher_list as $a_teacher)
        {
            $abbre_name = $a_teacher->abbreviation;
            
            echo $abbre_name." : ";
            
            //array of teacher objects
            $full_alternatives = Teacher::abbreToFullnameSingleSetup($abbre_name);
            
            foreach($full_alternatives as $a_name_object)
            {
                $teacher_accname = $a_name_object->accname;
                $teacher_fullname = $a_name_object->name;
                
                echo " ( ".$teacher_accname." , ".$teacher_fullname.") ";
            }
            
            echo "<br><br>";
        }
        
    }
     * 
     */
    
    /**
     * Return the list of teacher's name, followed by accname
     * @param string $type possible input: '' (empty string <-- default, means all), normal, temp
     * @return type 
     */
    public static function getTeahcerName($type='')
    {
        return array(array('fullname'=>'Chia Siew Eng', 'accname'=>'cse'),
            array('fullname'=>'Chin Yi Xuan Xuanie', 'accname'=>'cyx'), array('fullname'=>'Tian Zhe', 'accname'=>'tz'),
            array('fullname'=>'Jade Lim Swee Chern', 'accname'=>'lsc'), array('fullname'=>'Caris Ong ', 'accname'=>'co'));
    }
    
    
    /*
     The following functions are for testing purpose
     * 
     */
    
    //this function lists all abbre name (in teacher_list) that dont have a match
    //input : $teacher_list, a list of Teacher object, with abbre_name provided
    //output : na
    /* temporarily disabled. not updated
    public static function listUnmatchedAbbreName($teacher_list)
    {
        $db_url = Constant::db_url;
        $db_username = Constant::db_username;
        $db_password = Constant::db_password;
        $db_name = Constant::db_name;
        
        $db_con = mysql_connect($db_url, $db_username, $db_password);
        
        if (!$db_con)
        {
            die("function Teacher::listUnmatchedAbbreName : Could not connect to database");
        }
        
        mysql_select_db($db_name);
        
        $sql_query = "select abbre_name from ct_name_abbre_matching;";
        
        $sql_result = mysql_query($sql_query);
        
        $result_index = 0;
        $all_matched_abbre = Array();
        
        while($row = mysql_fetch_array($sql_result))
        {
            $all_matched_abbre[$result_index] = $row['abbre_name'];
            
            $result_index++;
        }
        
        $not_matched_abbre = Array();
        $not_matched_index = 0;
        
        foreach($teacher_list as $a_teacher)
        {
            $abbre = $a_teacher->abbreviation;
         
            if(!in_array($abbre, $all_matched_abbre))
            {
                $not_matched_abbre[$not_matched_index] = $abbre;
                $not_matched_index++;
            }
        }
        
        //print out 
        foreach($not_matched_abbre as $an_abbre)
        {
            echo "<br>";
            echo $an_abbre;
            echo "<br>";
        }
    }
     * 
     */
    
    /*
     The following are private functions
     * 
     */
    
    //this function finds a list of alternatives for abbre name of a single teacher
    //this function is used when the 1-to-1 match of abbre and full name is not established
    //input : abbre name - string
    //output : an array of teacher objects, with accname and fullname
    /* temporarily disabled. not updated
    private static function abbreToFullnameSingleSetup($teacher_abbre_name)
    {
        $result = Array();
        
        //algorithm to find a search token
        //normally will take the first token, but when the first token is a letter and the seond exist
        //the second is used
        
        $name_pieces = explode(" ", $teacher_abbre_name);
        $search_token = $name_pieces[0];
        
        $key_replacement = Constant::$abbre_token_replace;
        
        $teacher_abbre_name_modified = str_replace(' ', '_', $teacher_abbre_name);
        
        if(array_key_exists($teacher_abbre_name_modified, $key_replacement))
        {
            $search_token = $key_replacement[$teacher_abbre_name_modified];
        }
        else if(strlen($search_token)<=1)
        {
            if(isset($name_pieces[1]) && strlen($name_pieces[1])>1)
            {
                $search_token = $name_pieces[1];
            }
            else
            {
                return results;
            }
        }
        
        //search in database
        
        //use table astatek
        //$sql_query = "select user_id, user_name from actatek_user where user_position = 'Teacher' and user_name like '%".$search_token."%';";
        
        //user table fs_accounts_pri
        $sql_query = "select accname, accfullname from fs_accounts_pri where accfullname like '%".mysql_real_escape_string($search_token)."%';";
        
        $sql_result = mysql_query($sql_query);
        
        $result_index = 0;
        
        while($row = mysql_fetch_array($sql_result))
        {
            $oneAlternative = new Teacher($teacher_abbre_name);
            
            $oneAlternative->accname = $row['accname'];
            $oneAlternative->name = $row['accfullname'];
            
            $result[$result_index] = $oneAlternative;
            
            $result_index++;
        }
        
        return $result;
    }
     * 
     */
    
    //this function retrieve all teachers from database ifins
    private static function getAllTeachers()
    {
        $teacher_dict = Array();
        
        $ifins_db_url = Constant::ifins_db_url;
        $ifins_db_username = Constant::ifins_db_username;
        $ifins_db_password = Constant::ifins_db_password;
        $ifins_db_name = Constant::ifins_db_name;
        
        $ifins_db_con = mysql_connect($ifins_db_url, $ifins_db_username, $ifins_db_password);
        
        if (!$ifins_db_con)
        {
            return $teacher_dict;
        }
        
        mysql_select_db($ifins_db_name, $ifins_db_con);
        
        $sql_query_teacher = "select user_id, user_name, dept_name from student_details where user_position = 'Teacher';";
        $result_teacher = mysql_query($sql_query_teacher);
        if(!$result_teacher)
        {
            return $teacher_dict;
        }
        
        while($row = mysql_fetch_array($result_teacher))
        {
            $teacher_dict[$row['user_id']] = Array(
                'name' => $row['user_name'],
                'type' => $row['dept_name']
            );
        }
        
        return $teacher_dict;
    }
}

?>
