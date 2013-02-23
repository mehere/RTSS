<?php

Class Constant
{
    //database configuration
    const db_url = "localhost";
    const db_username = "root";
    const db_password = "passw0rd";
    const db_name = "ntu";

    const ifins_db_url = "localhost";
    const ifins_db_username = "root";
    const ifins_db_password = "passw0rd";
    const ifins_db_name = "ifins";

    //time slot
    const num_of_time_slot = 15;
    const num_of_week_day = 5;

    public static $mother_tongue = array("Chinese", "Tamil", "Malay");
    public static $teacher_type = array("Teacher"=>"Normal", "AED"=>"Aed", "Temp"=>"Temp", "HOD"=>"Hod", "untrained"=>"Untrained", "ExCo"=>"ExCo");   //key: types in database; value: types in websystem. Due to some reasons, we maintain the two list

    public static function connect_to_db($db_name)
    {
        if(strcmp($db_name, "ntu")===0)
        {
            $db_url = Constant::db_url;
            $db_username = Constant::db_username;
            $db_password = Constant::db_password;
            $db_name = Constant::db_name;

            $db_con = mysql_connect($db_url, $db_username, $db_password);

            if (!$db_con)
            {
                return null;
            }

            mysql_select_db($db_name);

            return $db_con;
        }
        else if(strcmp($db_name, "ifins")===0)
        {
            $ifins_db_url = Constant::ifins_db_url;
            $ifins_db_username = Constant::ifins_db_username;
            $ifins_db_password = Constant::ifins_db_password;
            $ifins_db_name = Constant::ifins_db_name;

            $ifins_db_con = mysql_connect($ifins_db_url, $ifins_db_username, $ifins_db_password);

            if (!$ifins_db_con)
            {
                return null;
            }

            mysql_select_db($ifins_db_name, $ifins_db_con);

            return $ifins_db_con;
        }
        else
        {
            return null;
        }
    }
    
    public static function sql_execute($db_con, $sql)
    {
        $query_result = mysql_query($sql, $db_con);
        
        if(!$query_result)
        {
            return null;
        }
        else if(!is_bool($query_result))
        {
            $result = Array();

            while($row = mysql_fetch_assoc($query_result))
            {
                $result[] = $row;
            }

            return $result;
        }
        else
        {
            return true;
        }
    }
}

?>
