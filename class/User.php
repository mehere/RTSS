<?php
spl_autoload_register(function($class){
    require_once "$class.php";
});

class User
{
    /**
     * this function verify user login for both admin and teacher
     * @param type $username
     * @param type $password
     * @return array output['type'] = teacher (teacher login, call output['accname']); output['type'] = admin (admin login); output['type'] = super_admin (super admin login); output['type'] = "" (wrong username or password)
     */
    public static function login($username, $password)
    {
        $result = array(
            'accname' => '',
            'type' => ''
        );

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

            $teacher_id = User::queryTeacherID($row['accfullname'], $username);

            $result['accname'] = $teacher_id;
            $result['type'] = "teacher";
            $result['fullname'] = $row['accfullname'];
        }
        else
        {
            $ifins_sql_query_pri = "select * from fs_accounts_pri where accname ='".mysql_real_escape_string(trim($username))."' and accpw = '".mysql_real_escape_string(trim($password))."';";
            $ifins_login_result_pri = Constant::sql_execute($ifins_db_con, $ifins_sql_query_pri);

            if(!is_null($ifins_login_result_pri) && count($ifins_login_result_pri) > 0)
            {
                $row_pri = $ifins_login_result_pri[0];

                $teacher_id = User::queryTeacherID($row_pri['accfullname'], $username);

                $result['accname'] = $teacher_id;
                $result['type'] = "teacher";
                $result['fullname'] = $row_pri['accfullname'];
            }
            else
            {
                $ifins_sql_query_sec = "select * from fs_accounts_sec where accname ='".mysql_real_escape_string(trim($username))."' and accpw = '".mysql_real_escape_string(trim($password))."';";
                $ifins_login_result_sec = Constant::sql_execute($ifins_db_con, $ifins_sql_query_sec);

                if(!is_null($ifins_login_result_sec) && count($ifins_login_result_sec) > 0)
                {
                    $row_sec = $ifins_login_result_sec[0];

                    $teacher_id = User::queryTeacherID($row_sec['accfullname'], $username);

                    $result['accname'] = $teacher_id;
                    $result['type'] = "teacher";
                    $result['fullname'] = $row_sec['accfullname'];
                }
            }
        }

        if(empty($result['accname']))
        {
            //no such teacher
            $result['type'] = "";
            return $result;
        }

        //admin
        $db_con = Constant::connect_to_db('ntu');

        if (empty($db_con))
        {
            $result['type'] = "";
            return $result;
        }

        $sql_query = "select * from admin where teacher_id ='".$result['accname']."';";
        $admin_login = Constant::sql_execute($db_con, $sql_query);

        if(!is_null($admin_login) && count($admin_login) > 0)
        {
            $is_super = $admin_login[0]['is_super'];

            if($is_super)
            {
                $result['type'] = "super_admin";
            }
            else
            {
                $result['type'] = "admin";
            }
            
            /*
            //mark lock
            $sql_lock_login = "update admin set is_login = 'SCHEDULER' where teacher_id = '".$result['accname']."';";
            $lock_login = Constant::sql_execute($db_con, $sql_lock_login);

            if($lock_login)
            {
                return $result;
            }
            else
            {
                $result['type'] = "";
                
                return $result;
            }
             * 
             */
        }
        
        return $result;
    }

    /**
     * check if there is admin using it, if yes,
     * @param string $userID teacher ID
     * @param string $area value : SCHEDULER
     * @param bool $isSuperAdmin default : false
     * @return bool false : if there is an admin and the login user is not super admin; true: if there is no user and current login admin lock the process
     */
    public static function lock($userID, $area, $isSuperAdmin = false)
    {
        $db_con = Constant::connect_to_db('ntu');

        if (empty($db_con))
        {
            return false;
        }

        $sql_check_login = "select * from admin where is_login = '$area';";
        $check_login = Constant::sql_execute($db_con, $sql_check_login);
        if(is_null($check_login))
        {
            return false;
        }

        if(count($check_login) === 0)
        {
            //no admin currently login
            $sql_lock_login = "update admin set is_login = '$area' where teacher_id = '$userID';";
            $check_login = Constant::sql_execute($db_con, $sql_lock_login);
            
            if($check_login)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            //admin logged in alr
            if($isSuperAdmin)
            {
                $old_admin = $check_login[0]['teacher_id'];
                
                //logout old admin
                $sql_clear_login = "update admin set is_login = NULL where teacher_id = '$old_admin';";
                $clear_login = Constant::sql_execute($db_con, $sql_clear_login);

                if($clear_login)
                {
                    return true;
                }
                else
                {
                    return false;
                }
                
                //set new login
                $sql_lock_login = "update admin set is_login = '$area' where teacher_id = '$userID';";
                $lock_login = Constant::sql_execute($db_con, $sql_lock_login);

                if($lock_login)
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }
        }
    }

    public static function unlock($accname, $area)
    {
        $db_con = Constant::connect_to_db('ntu');

        if (empty($db_con))
        {
            return false;
        }
        
        $sql_unlock = "update admin set is_login = NULL where teacher_id = '$accname' and is_login = '$area';";
        $unlock = Constant::sql_execute($db_con, $sql_unlock);
        
        if(is_null($unlock))
        {
            return false;
        }
        
        return true;
    }
    
    /**
     * 
     * @param string $area value : SCHEDULER, EDIT_SCHEDULE
     * @return string/null string with login accname, null if no admin login
     */
    public static function checkLogin($area)
    {
        $db_con = Constant::connect_to_db('ntu');

        if (empty($db_con))
        {
            throw new DBException("fail to check log in", __FILE__, __LINE__);
        }

        $sql_check_login = "select * from admin where is_login = '$area';";
        $check_login = Constant::sql_execute($db_con, $sql_check_login);
        if(is_null($check_login))
        {
            throw new DBException("fail to check log in", __FILE__, __LINE__);
        }

        if(count($check_login) === 0)
        {
            return null;
        }
        else
        {
            $old_admin = $check_login[0]['teacher_id'];
            
            return $old_admin;
        }
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

    private static function queryTeacherID($fullname, $accname)
    {
        $ifins_db_con = Constant::connect_to_db('ifins');

        if (empty($ifins_db_con))
        {
            return "";
        }
        
        if(preg_match("/^[A-Z][0-9]{7}[A-Z]{1}$/", $accname))
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
