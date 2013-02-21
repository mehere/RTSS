<?php

Class Constant
{
    //database configuration
    const db_url = "localhost";
    const db_username = "root";
    const db_password = "";
    const db_name = "ntu";

    const ifins_db_url = "localhost";
    const ifins_db_username = "root";
    const ifins_db_password = "";
    const ifins_db_name = "ifins";

    //time slot
    const num_of_time_slot = 15;
    const num_of_week_day = 5;

    public static $time_conversion = array(
        0 => '0000',
        1 => '0725',
        2 => '0745',
        3 => '0815',
        4 => '0845',
        5 => '0915',
        6 => '0945',
        7 => '1015',
        8 => '1045',
        9 => '1115',
        10 => '1145',
        11 => '1215',
        12 => '1245',
        13 => '1315',
        14 => '1345',
        15 => '1415'
    );

    public static $inverse_time_conversion = array(
        '0725' => 1,
        '0745' => 2,
        '0815' => 3,
        '0845' => 4,
        '0915' => 5,
        '0945' => 6,
        '1015' => 7,
        '1045' => 8,
        '1115' => 9,
        '1145' => 10,
        '1215' => 11,
        '1245' => 12,
        '1315' => 13,
        '1345' => 14,
        '1415' => 15
    );

    public static $mother_tongue = array("Chinese", "Tamil", "Malay");
    public static $teacher_type = array("Teacher"=>"Normal", "AED"=>"Aed", "Temp"=>"Temp", "HOD"=>"Hod", "untrained"=>"Untrained", "ExCo"=>"ExCo");   //key: types in database; value: types in websystem. Due to some reasons, we maintain the two list

    //replacement of search token when matching abbre name and full name
    //in Teacher::abbreToFullnameSingleSetup()
    public static $abbre_token_replace = array(
        'BOSWELLC' => 'Boswell',
        'CHAN_CHR' => 'liang',
        'CHEE_G' => 'lai kai',
        'CHOONG_A' => 'foong',
        'GANCANDY' => 'candy',
        'GOHWENDY' => 'goh',
        'HEFANG' => 'fang',
        'HO_YK' => 'Yok',
        'HUM_JUST' => 'kian',
        'IAHMAD' => 'AHMAD',
        'LEE_MARY' => 'gei',
        'LEEAGNES' => 'AGNES',
        'LIMJOHN' => 'Johnstone',
        'LIMLAURA' => 'LAURA',
        'NG_JADE' => 'jade',
        'ONGCARIS' => 'CARIS',
        'TOHHAZEL' => 'TOH',
        'WEESHEON' => 'yuin',
        'WONGBER' => 'WONG'
    );

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

    public static function sql_execute($db, $sql)
    {
        $db_con = Constant::connect_to_db($db);
        if(!$db_con)
        {
            return null;
        }

        $query_result = mysql_query($sql);

        if(!$query_result)
        {
            return null;
        }
        else
        {
            $result = Array();

            while($row = mysql_fetch_assoc($query_result))
            {
                $result[] = $row;
            }

            return $result;
        }
    }
}

?>
