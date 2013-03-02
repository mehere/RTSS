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
        $ifins_db_con = Constant::connect_to_db('ifins');
        
        if (empty($ifins_db_con))
        {
            return $result;
        }
        
        $ifins_sql_query = "select * from fs_accounts where accname ='".mysql_real_escape_string(trim($username))."' and accpw = '".mysql_real_escape_string(trim($password))."';";
        $ifins_login_result = Constant::sql_execute($ifins_db_con, $ifins_sql_query);
        
        if(!is_null($ifins_login_result) && count($ifins_login_result) > 0)
        {
            $row = $ifins_login_result[0];
            
            //$teacher_id = User::queryTeacherID($username, $row['accfullname']);
            
            //if(empty($teacher_id))
            //{
            //    return $result;
           // }
            //else
            //{
                $result['accname'] = $teacher_id;
                $result['type'] = "teacher";
                $result['fullname'] = $row['accfullname'];
                return $result;
           // }
        }
        
        $ifins_sql_query_pri = "select * from fs_accounts_pri where accname ='".mysql_real_escape_string(trim($username))."' and accpw = '".mysql_real_escape_string(trim($password))."';";
        $ifins_login_result_pri = Constant::sql_execute($ifins_db_con, $ifins_sql_query_pri);
        
        if(!is_null($ifins_login_result_pri) && count($ifins_login_result_pri) > 0)
        {
            $row_pri = $ifins_login_result_pri[0];
            
            //$teacher_id = User::queryTeacherID($username, $row_pri['accfullname']);
            
            //if(empty($teacher_id))
            //{
            //    return $result;
            //}
            //else
            //{
                $result['accname'] = $teacher_id;
                $result['type'] = "teacher";
                $result['fullname'] = $row_pri['accfullname'];
                return $result;
            //}
        } 
        
        $ifins_sql_query_sec = "select * from fs_accounts_sec where accname ='".mysql_real_escape_string(trim($username))."' and accpw = '".mysql_real_escape_string(trim($password))."';";
        $ifins_login_result_sec = Constant::sql_execute($ifins_db_con, $ifins_sql_query_sec);
        
        if(!is_null($ifins_login_result_sec) && count($ifins_login_result_sec) > 0)
        {
            $row_sec = $ifins_login_result_sec[0];
            
            //$teacher_id = User::queryTeacherID($username, $row_sec['accfullname']);
            
            //if(empty($teacher_id))
            //{
            //    return $result;
            //}
            //else
            //{
            
                $result['accname'] = $teacher_id;
                $result['type'] = "teacher";
                $result['fullname'] = $row_sec['accfullname'];
                return $result;
            //}
        } 
        
        //admin
        $db_con = Constant::connect_to_db('ntu');
        
        if (empty($db_con))
        {
            return $result;
        }
        
        $sql_query = "select * from admin where username ='".mysql_real_escape_string(trim($username))."' and password = '".mysql_real_escape_string(trim($password))."';";
        $admin_login = Constant::sql_execute($db_con, $sql_query);
        
        if(!is_null($admin_login) && count($admin_login) > 0)
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
    /*
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
     * 
     */
    
    private static function queryTeacherID($accname, $fullname)
    {
        $ifins_db_con = Constant::connect_to_db('ifins');
        
        if (empty($ifins_db_con))
        {
            return "";
        }
        
        if(preg_match("/^S-[0-9]{7}-[A-Z]{1}$/", $accname))
        {
            $teacher_id = substr($accname, 1, 7);

            $sql_query_teacher_id = "select * from actatek_user where user_id = '".mysql_real_escape_string($teacher_id)."';";
            $query_teacher_id_result = Constant::sql_execute($ifins_db_con, $sql_query_teacher_id);

            if(empty($query_teacher_id_result))
            {
                return "";
            }
            $row = $query_teacher_id_result[0];
            if($row)
            {
                return $teacher_id;
            }
        }

        $sql_query_teacher_id_via_name = "select * from actatek_user where user_name = '".mysql_real_escape_string(trim($fullname))."';";
        $query_teacher_id_via_name_result = Constant::sql_execute($ifins_db_con, $sql_query_teacher_id_via_name);

        if(empty($query_teacher_id_via_name_result))
        {
            return "";
        }

        $row = $query_teacher_id_via_name_result[0];
        if($row)
        {
           return $row['user_id']; 
        }
        
        return "";
    }
}
?>
