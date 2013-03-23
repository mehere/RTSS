<?php
class Template
{
    // Main menu display
    const HOME="Scheduler";
    const TT_VIEW="View Timetable";
    const TT_ADMIN="Upload Timetable";
    const REPORT="Report";
    
    // Submenu display
    const SCHEDULE="Schedule";
    const SMS="SMS Console";
    
    const TT_ADMIN_MASTER="Upload Master Timetable";
    const TT_ADMIN_AED="Upload AED/Relief Timetable";
    
    const REPORT_OVERALL='Overall';
    const REPORT_INDIVIDUAL='Indivudal';
    
    // Menu map
    private static $MAIN_MENU=array(
        self::HOME => "/RTSS/relief/",
        self::TT_VIEW => "/RTSS/timetable/",
        self::TT_ADMIN => "/RTSS/upload/",
        self::REPORT => "/RTSS/report/"
    );
    
    private static $SUBMENU=array(
        self::HOME => array(
            self::SCHEDULE => "/RTSS/relief/",
            self::SMS => "/RTSS/sms/"            
        ),
        
        self::TT_VIEW => array(            
        ),
        
        self::TT_ADMIN => array(
            self::TT_ADMIN_MASTER => "/RTSS/upload/",
            self::TT_ADMIN_AED => "/RTSS/upload/AED.php"
        ),
        
        self::REPORT => array(
            self::REPORT_OVERALL => "/RTSS/report",
            self::REPORT_INDIVIDUAL => "/RTSS/report/individual.php"
        )
    );
    
    /**
     * 
     * @param type $isController default false
     * @param type $needsJSON default false
     * @param type $allowsAll default false
     * @return void
     */
    public static function validate($isController=false, $needsJSON=false, $allowsAll=false)
    {
        session_start();
        
        if ($isController)
        {
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
        }

        if (!$_SESSION['accname'] || (!$allowsAll && $_SESSION['type'] != 'admin'))
        {
            if ($needsJSON)
            {
                header('Content-type: application/json');
                echo json_encode(array('error'=>1));
                return;
            }
            else
            {
                header("Location: /RTSS/");
                exit;
            }
        }
    }

    public static function printHeaderAndDoValidation($title, $css, $scripts, $mainMenuSelect, $submenuTitle, $submenuSelect='', $allowsAll=false)
    {
        self::validate(false, false, $allowsAll);
        
        $title=PageConstant::SCH_NAME_ABBR . " " . PageConstant::PRODUCT_NAME . " - " . $title;
        
        // Menu part
        if ($_SESSION['type'] != 'admin')
        {
            self::$MAIN_MENU=array_slice(self::$MAIN_MENU, 1, 1);
        }
        
        $menuPart='';
        foreach (self::$MAIN_MENU as $key => $value)
        {
            $active=$key == $mainMenuSelect ? "active" : '';
            $menuPart .= <<< EOD
<li class="menu-item $active">
    <a class="menu" href="$value">
        $key
    </a>
</li>
EOD;
        }
        
        // Submenu part
        $submenuPart='';
        foreach (self::$SUBMENU[$mainMenuSelect] as $key => $value)
        {
            $active=$key == $submenuSelect ? "active" : '';
            $submenuPart .= <<< EOD
<li class="submenu-item $active">
    <a class="submenu" href="$value">
        $key
    </a>
</li>
EOD;
        }

        // HTML of header
        echo <<< EOD
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />      
        <title>$title</title>
        <link href="/RTSS/jquery-ui/css/jQui1.9.2.min.css" rel="stylesheet" type="text/css" />
        <link href="/RTSS/css/main.css" rel="stylesheet" type="text/css" />
EOD;
        foreach ($css as $aCss)
        {
            echo <<< EOD
        <link href="/RTSS/css/$aCss" rel="stylesheet" type="text/css" />
EOD;
        }
        echo <<< EOD
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js" type="text/javascript"></script>
        <script src="/RTSS/jquery-ui/jQui1.9.2.min.js" type="text/javascript"></script>
        <script src="/RTSS/js/config.js" type="text/javascript"></script>
EOD;
        foreach ($scripts as $script)
        {
            echo <<< EOD
        <script src="/RTSS/js/$script" type="text/javascript"></script>
EOD;
        }

        $username=htmlentities($_SESSION['username']);
        echo <<< EOD
    </head>
    <body>
        <div id="container">
            <div class="header">
                <div class="header-top">
                    <a href="/RTSS/relief/" title="Home"><img src="/RTSS/img/school-logo.png" class="logo" /></a>
                    <div class="wrapper">
                        <div class="statusBar">
                            <div class="statusbar-item">
                                <span class="statusbar">
                                    $username
                                </span>
                            </div>
                            <div class="statusbar-item">
                                <a class="statusbar" href="/RTSS/_logout.php">
                                    Log out
                                </a>
                            </div>
                            <div style="clear:both;"></div>
                        </div>
                        <div style="clear:both;"></div>

                        <ul class="menubar">
                            $menuPart
                        </ul>                        
                        <div style="clear:both;"></div>
                    </div>
                    <div style="clear:both;"></div>
                </div>
                <div style="clear:both;"></div>

                <div class="submenu">
                    <div class="submenu-title">
                        <h1 class="submenu-title">
                            $submenuTitle
                        </h1>
                    </div>
                    <ul class="submenubar">
                        $submenuPart
                    </ul>

                    <div style="clear:both;"></div>

                    <img class="submenu-separator" src="/RTSS/img/line.png"/>
                </div>
            </div>
            <div style="clear:both;"></div>
            <div class="content">
EOD;
    }

    public static function printFooter()
    {
        $date=date("Y");
        $schoolName=PageConstant::SCH_NAME;
        echo <<< EOD
            </div>
            <div class="footer">
                <span>Copyright @ $date $schoolName
                    <span style="font-size: .8em; margin-left: 10px">All rights reserved</span>
                </span>
            </div>
        </div>
    </body>
</html>
EOD;
    }

}
?>