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
