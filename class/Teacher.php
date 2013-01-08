<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Teacher
 *
 * @author Wee
 */

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
    public static function getTeachersAccnameAndFullname($teacher_list)
    {
        $db_url = Constant::db_url;
        $db_username = Constant::db_username;
        $db_password = Constant::db_password;
        $db_name = Constant::db_name;
        
        $db_con = mysql_connect($db_url, $db_username, $db_password);
        
        if (!$db_con)
        {
            die("function Teacher::abbreToFullnameBatch : Could not connect to database");
        }
        
        mysql_select_db($db_name, $db_con);
        
        $result_list = Array();
        
        foreach($teacher_list as $key => $a_teacher)
        {
            $sql_query = "select ct_name_abbre_matching.acc_name, ac_all_teacher.name 
                from ct_name_abbre_matching , ac_all_teacher where ct_name_abbre_matching.acc_name = ac_all_teacher.acc_name
                and ct_name_abbre_matching.abbre_name = '".$a_teacher->abbreviation."';";
            $result = mysql_query($sql_query);
            
            $temp_teacher = new Teacher($a_teacher->abbreviation);
            
            if($result)
            {
                $row = mysql_fetch_array($result);
                $temp_teacher->name=$row['name'];
                $temp_teacher->accname=$row['acc_name'];
            }
            
            $result_list[$key] = $temp_teacher;
        }
        
        mysql_close($db_con);
        
        return $result_list;
    }
    
    //this function finds a list of alternatives for abbre name of all teachers
    //this function is used when the 1-to-1 match of abbre and full name is not established
    //input : an array of teacher objects, with abbre name provided
    //output : NA
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
        
        mysql_close($db_con);
    }
    
    /*
     The following functions are for testing purpose
     * 
     */
    
    //this function lists all abbre name (in teacher_list) that dont have a match
    //input : $teacher_list, a list of Teacher object, with abbre_name provided
    //output : na
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
        
        mysql_close($db_con);
        
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
    
    /*
     The following are private functions
     * 
     */
    
    //this function finds a list of alternatives for abbre name of a single teacher
    //this function is used when the 1-to-1 match of abbre and full name is not established
    //input : abbre name - string
    //output : an array of teacher objects, with accname and fullname
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
        $sql_query = "select accname, accfullname from fs_accounts_pri where accfullname like '%".$search_token."%';";
        
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
}

?>
