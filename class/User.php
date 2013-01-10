<?php
class User
{
    //this function verify user login for both admin and teacher
    //input : accname, password
    //output : "teacher" or "admin" if success. Empty if username does not exist or passowrd is incorrect
    public static function login($username, $password)
    {
        //teacher
        $ifins_db_url = Constant::ifins_db_url;
        $ifins_db_username = Constant::ifins_db_username;
        $ifins_db_password = Constant::ifins_db_password;
        $ifins_db_name = Constant::ifins_db_name;
        $ifins_table_teacher_veri = Constant::ifins_table_teacher_verification;
        
        $ifins_db_con = mysql_connect($ifins_db_url, $ifins_db_username, $ifins_db_password);
        
        if (!$ifins_db_con)
        {
            return "";
        }
        
        mysql_select_db($ifins_db_name, $ifins_db_con);
        
        $ifins_sql_query = "select * from ".$ifins_table_teacher_veri." where accname ='".$username."' and accpw = '".$password."';";
        if(mysql_fetch_array(mysql_query($ifins_sql_query)))
        {
            mysql_close($ifins_db_con);
            return "teacher";
        } 
        
        mysql_close($ifins_db_con);
        
        //admin
        $db_url = Constant::db_url;
        $db_username = Constant::db_username;
        $db_password = Constant::db_password;
        $db_name = Constant::db_name;
        
        $db_con = mysql_connect($db_url, $db_username, $db_password);
        
        if (!$db_con)
        {
            return "";
        }
        
        mysql_select_db($db_name, $db_con);
        
        $sql_query = "select * from x_user where username ='".$username."' and password = '".$password."';";
        if(mysql_fetch_array(mysql_query($sql_query)))
        {
            mysql_close($db_con);
            return "admin";
        } 
        
        mysql_close($db_con);
        
        return "";
    }
    
    //this function add a new admin to database
    //input : username, password
    //output : true or false
    //note : password is not encrypted
    public static function add_admin($username, $password)
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
        
        $sql_query_check_teacher = "select * from ac_all_teacher where acc_name = '".$username."';";
        if(mysql_fetch_array(mysql_query($sql_query_check_teacher)))
        {
            mysql_close($db_con);
            return false;
        }
        
        $sql_query_check_admin = "select * from x_user where username = '".$username."'";
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
    
    //this function delete an admin from database
    //input : username
    //output : return true if delete successfully or the database doesn't have the username. reutrn false if fail to delete
    public static function delete_admin($username)
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
        
        $sql_query_delete_admin = "delete from x_user where username = '".$username."';";
        
        if(!mysql_query($sql_query_delete_admin))
        {
            mysql_close($db_con);
            return false;
        }
        
        mysql_close($db_con);
        
        return true;
    }
}
?>
