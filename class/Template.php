<?php
class Template
{
    // Main menu display
    const HOME=<<< EOD
<img class="menu" src="/RTSS/img/home.png"/>   
EOD;
    const TT_VIEW="View Timetable";
    const TT_ADMIN="Upload Timetable";
    const REPORT="Report";
    
    private static $MAIN_MENU=array(
        self::HOME => "/RTSS/",
        self::TT_VIEW => "/RTSS/timetable/",
        self::TT_ADMIN => "/RTSS/timetable/admin.php",
        self::REPORT => "/RTSS/report/"        
    );
    
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

    public static function printHeaderAndDoValidation($title, $css, $scripts, $mainMenuSelect, $submenuSelect, $allowsAll=false)
    {
        self::validate(false, false, $allowsAll);

        //$mainMenu = array("<img class='menu' src='/RTSS/resources/images/home.png'/>", "Upload Timetable", "View Reports");
        
        $mainMenuLinks = array("abc", "abc", "abc");
        $submenu0 = array("");
        $submenu1 = array("Upload Master CSV Timetabl", "Upload Aed Timetable");
        $submenu = array($submenu0, $submenu1);
        
        $title=PageConstant::SCH_NAME_ABBR . " " . PageConstant::PRODUCT_NAME . " - " . $title;
        
        // Menu part
        $menuPart='';
        foreach (self::$MAIN_MENU as $key => $value)
        {
            $menuPart .= <<< EOD
<div class="menu-item">
    <a class="menu" href="$value">
        <span class="menu">
            $key
        </span>
    </a>
</div>
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
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
        <script src="/RTSS/jquery-ui/jQui1.9.2.min.js"></script>
EOD;
        foreach ($scripts as $script)
        {
            echo <<< EOD
        <script src="/RTSS/js/$script"></script>
EOD;
        }

        $username=htmlentities($_SESSION['username']);
        echo <<< EOD
    </head>
    <body>
        <div class="container">
            <div class="header">
                <div class="header-top">
                    <img src="/RTSS/img/school-logo.png" class="logo" />
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

                        <div class="menubar">
                            <div class="menu-foreground">
                                $menuPart                                
                            <div style="clear:both;"></div>
                            </div>
                        </div>                        
                        <div style="clear:both;"></div>
                    </div>
                    <div style="clear:both;"></div>
                </div>
                <div style="clear:both;"></div>

                <div class="submenu">
                    <div class="submenu-title">
                        <h1 class="submenu-title">
                            <a class="submenu-title">Upload Timetable</a>
                        </h1>
                    </div>
                    <div class="submenubar">
                        <div class="submenu-item first">
                            <a class="submenu">
                                <span class="submenu">
                                    Upload Master CSV Timetable
                                </span>
                            </a>
                        </div>
                        <div class="submenu-item">
                            <a class="submenu">
                                <span class="submenu">
                                    Upload Aed Timetable
                                </span>
                            </a>
                        </div>
                    </div>

                    <div style="clear:both;"></div>

                    <img class="submenu-separator" src="/RTSS/img/line.png"/>
                </div>
            </div>
            <div style="clear:both;"></div>
            <div class="content">
EOD;
//        "        <div class='container'>
//            <div class='header'>
//                <div class='header-top'>
//                    <img src='/RTSS/resources/images/school-logo.png' class='logo' />
//                    <div class='wrapper'>
//                        <div class='statusBar'>
//                            <div class='statusbar-item'>
//                                <span class='statusbar'>
//";
//
//        /// To-Do: print user name
//
//        echo
//        "                                </span>
//                            </div>
//                            <div class='statusbar-item'>
//                                <a class='statusbar'>
//                                    Log out
//                                </a>
//                            </div>
//                            <div style='clear:both;'></div>
//                        </div>
//                        <div style='clear:both;'></div>
//
//                        <div class='menubar'>
//                            <div class='menu-foreground'>
//";
//        for ($i = 0; $i < count($mainMenu); $i++)
//        {
//            echo
//            "                                <div class='menu-item";
//            if ($i == $mainIndex)
//            {
//                echo " active";
//            }
//
//            echo
//            "'>
//                                    <a class='menu' href='";
//            echo "$mainMenuLinks[$i]";
//            echo
//            "'>
//                                        <span class='menu'>
//";
//
//            echo $mainMenu[$i];
//            echo
//            "
//                                        </span>
//                                    </a>
//                                </div>
//";
//        }
//
//
//        echo
//        " <div style='clear:both;'></div>
//                            </div>
//                        </div>
//                        <div style='clear:both;'></div>
//                    </div>
//                    <div style='clear:both;'></div>
//                </div>
//                <div style='clear:both;'></div>
//";
//
//
//        // submenu
//        echo
//        "                <div class='submenu'>
//";
//        if ($mainIndex != 0)
//        {
//            echo
//            "                    <div class='submenu-title'>
//                        <h1 class='submenu-title'>
//                            <a class='submenu-title'>";
//            echo $mainMenu[$mainIndex];
//            echo
//            "
//                            </a>
//                        </h1>
//                    </div>
//";
//        }
//
//        // submenu bar
//        echo
//        "                   <div class='submenubar'>";
//        $thisSubMenu = $submenu[$mainIndex];
//        for ($i = 0; $i < count($thisSubMenu); $i++)
//        {
//            echo
//            "<div class='submenu-item";
//
//            if ($i == 0)
//            {
//                echo " first";
//            }
//            echo "'>
//                            <a class='submenu'>
//                                <span class='submenu'>
//                                    ";
//            echo $thisSubMenu[$i];
//            echo
//            "
//                                </span>
//                            </a>
//                        </div>";
//        }
//
//        echo
//        "                    </div>
//                    <div style='clear:both;'></div>
//                    <img class='submenu-separator' src='/RTSS/resources/images/line.png'/>
//                </div>
//            </div>
//            <div style='clear:both;'></div>
//            <div class='content'>
//";
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