<?php
require_once 'util.php';

class User
{
    //this function verify user login for both admin and teacher
    //input : accname, password
    //output : array as specified in docs. empty($output['type']) if no such user or password wrong. 
    public static function login($username, $password)
    {
        $result = Array(
            'accname' => '',
            'type' => '',
        );
        
        //teacher
        $ifins_db_url = Constant::ifins_db_url;
        $ifins_db_username = Constant::ifins_db_username;
        $ifins_db_password = Constant::ifins_db_password;
        $ifins_db_name = Constant::ifins_db_name;
        
        $ifins_db_con = mysql_connect($ifins_db_url, $ifins_db_username, $ifins_db_password);
        
        if (!$ifins_db_con)
        {
            return $result;
        }
        
        mysql_select_db($ifins_db_name, $ifins_db_con);
        
        $ifins_sql_query = "select * from fs_accounts where accname ='".mysql_real_escape_string($username)."' and accpw = '".mysql_real_escape_string($password)."';";
        $ifins_login_result = mysql_query($ifins_sql_query);
        
        if(!$ifins_login_result)
        {
            return $result;
        }
        
        $row = mysql_fetch_assoc($ifins_login_result);
        if($row)
        {
            $teacher_id = User::queryTeacherID($username, $row['accfullname']);
            
            if(empty($teacher_id))
            {
                return $result;
            }
            else
            {
                $result['accname'] = $teacher_id;
                $result['type'] = "teacher";
                $result['fullname'] = $row['accfullname'];
                return $result;
            }
        }
        
        $ifins_sql_query_pri = "select * from fs_accounts_pri where accname ='".mysql_real_escape_string($username)."' and accpw = '".mysql_real_escape_string($password)."';";
        $ifins_login_result_pri = mysql_query($ifins_sql_query_pri);
        
        if(!$ifins_login_result_pri)
        {
            return $result;
        }
        
        $row_pri = mysql_fetch_assoc($ifins_login_result_pri);
        if($row_pri)
        {
            $teacher_id = User::queryTeacherID($username, $row_pri['accfullname']);
            
            if(empty($teacher_id))
            {
                return $result;
            }
            else
            {
                $result['accname'] = $teacher_id;
                $result['type'] = "teacher";
                $result['fullname'] = $row['accfullname'];
                return $result;
            }
        } 
        
        $ifins_sql_query_sec = "select * from fs_accounts_sec where accname ='".mysql_real_escape_string($username)."' and accpw = '".mysql_real_escape_string($password)."';";
        $ifins_login_result_sec = mysql_query($ifins_sql_query_sec);
        
        if(!$ifins_login_result_sec)
        {
            return $result;
        }
        
        $row_sec = mysql_fetch_assoc($ifins_login_result_sec);
        if($row_sec)
        {
            $teacher_id = User::queryTeacherID($username, $row_sec['accfullname']);
            
            if(empty($teacher_id))
            {
                return $result;
            }
            else
            {
                $result['accname'] = $teacher_id;
                $result['type'] = "teacher";
                $result['fullname'] = $row['accfullname'];
                return $result;
            }
        } 
        
        //admin
        $db_url = Constant::db_url;
        $db_username = Constant::db_username;
        $db_password = Constant::db_password;
        $db_name = Constant::db_name;
        
        $db_con = mysql_connect($db_url, $db_username, $db_password);
        
        if (!$db_con)
        {
            return $result;
        }
        
        mysql_select_db($db_name, $db_con);
        
        $sql_query = "select * from x_user where username ='".mysql_real_escape_string($username)."' and password = '".mysql_real_escape_string($password)."';";
        if(mysql_fetch_array(mysql_query($sql_query)))
        {
            $result['accname'] = $username;
            $result['type'] = "admin";
            $result['fullname'] = 'admin';
            return $result;
        } 
        
        return $result;
    }
    
    //this function add a new admin to database
    //input : username, password
    //output : true or false
    //note : password is not encrypted
    /* temporarily disabled, not updated
    public static function addAdmin($username, $password)
    {
        $db_url = Constant::db_url;
        $db_username = Constant::db_username;
        $db_password = Constant::db_password;
        $db_name = Constant::db_name;
        
        $db_con = mysql_connect($db_url, $db_username, $db_password);
        
        if (!$db_con)
        {
            return false;
        }
        
        mysql_select_db($db_name, $db_con);
        
        $sql_query_check_teacher = "select * from ac_all_teacher where acc_name = '".mysql_real_escape_string($username)."';";
        if(mysql_fetch_array(mysql_query($sql_query_check_teacher)))
        {
            mysql_close($db_con);
            return false;
        }
        
        $sql_query_check_admin = "select * from x_user where username = '".mysql_real_escape_string($username)."'";
        if(mysql_fetch_array(mysql_query($sql_query_check_admin)))
        {
            mysql_close($db_con);
            return false;
        }
        
        $sql_query_insert = "insert into x_user values ('".mysql_real_escape_string($username)."' , '".mysql_real_escape_string($password)."');";
        if(!mysql_query($sql_query_insert))
        {
            mysql_close($db_con);
            return false;
        }
        
        mysql_close($db_con);
        
        return true;
    }
     * 
     */
    
    //this function delete an admin from database
    //input : username
    //output : return true if delete successfully or the database doesn't have the username. reutrn false if fail to delete
    public static function deleteAdmin($username)
    {
        $db_url = Constant::db_url;
        $db_username = Constant::db_username;
        $db_password = Constant::db_password;
        $db_name = Constant::db_name;
        
        $db_con = mysql_connect($db_url, $db_username, $db_password);
        
        if (!$db_con)
        {
            return false;
        }
        
        mysql_select_db($db_name, $db_con);
        
        $sql_query_delete_admin = "delete from x_user where username = '".mysql_real_escape_string($username)."';";
        
        if(!mysql_query($sql_query_delete_admin))
        {
            return false;
        }
        
        return true;
    }
    
    private static function queryTeacherID($accname, $fullname)
    {
        $ifins_db_url = Constant::ifins_db_url;
        $ifins_db_username = Constant::ifins_db_username;
        $ifins_db_password = Constant::ifins_db_password;
        $ifins_db_name = Constant::ifins_db_name;
        
        $ifins_db_con = mysql_connect($ifins_db_url, $ifins_db_username, $ifins_db_password);
        
        if (!$ifins_db_con)
        {
            return "";
        }
        
        mysql_select_db($ifins_db_name, $ifins_db_con);
        
        if(preg_match("/^S-[0-9]{7}-[A-Z]{1}$/", $accname))
        {
            $teacher_id = substr($accname, 1, 7);

            $sql_query_teacher_id = "select * from actatek_user where user_id = '".mysql_real_escape_string($teacher_id)."';";
            $query_teacher_id_result = mysql_query($sql_query_teacher_id);

            if(!$query_teacher_id_result)
            {
                return "";
            }
            if(mysql_fetch_assoc($query_teacher_id_result))
            {
                return $teacher_id;
            }
        }

        $sql_query_teacher_id_via_name = "select * from actatek_user where user_name = '".mysql_real_escape_string($fullname)."';";
        $query_teacher_id_via_name_result = mysql_query($sql_query_teacher_id_via_name);

        if(!$query_teacher_id_via_name_result)
        {
            return "";
        }

        $row = mysql_fetch_assoc($query_teacher_id_via_name_result);
        if($row)
        {
           return $row['user_id']; 
        }
        
        return "";
    }
}
?>
