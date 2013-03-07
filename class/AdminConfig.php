<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

spl_autoload_register(function($class){
    require_once "$class.php";
});

class AdminConfig
{
    public static function setRecommendedLesson($num)
    {
        if(!is_int($num))
        {
            return false;
        }
        
        $db_con = Constant::connect_to_db('ntu');
        if(empty($db_con))
        {
            return false;
        }
        
        $sql_set = "update admin_config set value = '$num' where identifier = 'recommended_num';";
        $set_result = Constant::sql_execute($db_con, $sql_set);
        if(is_null($set_result))
        {
            return false;
        }
    }
}

?>
