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
    const ifins_table_teacher_verification = "fs_accounts_pri";
    
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
    
    //error handling
    const default_var_value = "n.a.";
    const default_num_value = 0;
    
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
}

?>
