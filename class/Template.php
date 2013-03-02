<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Template
 *
 * @author Wee
 */
class Template
{

    public static function printHeaderAndDoValidation($allowsAll, $title, $css, $scripts, $mainIndex)
    {

        // To-Do: To check what kind of credentials is required
        session_start();
        if (!$allowsAll){

        }


        $mainMenu = array("<img class='menu' src='/RTSS/resources/images/home.png'/>", "Upload Timetable", "View Reports");
        $mainMenuLinks = array("abc", "abc", "abc");
        $submenu0 = array("");
        $submenu1 = array("Upload Master CSV Timetabl", "Upload Aed Timetable");
        $submenu = array($submenu0, $submenu1);

        echo
        "<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'/>
        <meta http-equiv='Cache-Control' content='no-cache, no-store, must-revalidate' />
        <meta http-equiv='Pragma' content='no-cache' />
        <meta http-equiv='Expires' content='0' />
        <title>";
        echo $title;
        echo
        "       </title>
        <link rel='stylesheet' type='text/css' href='weeUi.css'/>
        <link rel='stylesheet' type='text/css' href='/RTSS/resources/lib/jquery/css/flat/jquery-ui-1.10.1.custom.css' />
";
        foreach ($css as $aCss){
            echo
"        <link rel='stylesheet' type='text/css' href='";
            echo $aCss;
            echo
"'/>";
        }
        echo
"        <script src='/RTSS/resources/lib/jquery/js/jquery-1.9.1.js'></script>
        <script src='/RTSS/resources/lib/jquery/js/jquery-ui-1.10.1.custom.js'></script>
";
        foreach ($scripts as $script)
        {
            echo
            "      <script src='";
            echo $script;
            echo
            "'></script>";
        }

        echo
        "        <div class='container'>
            <div class='header'>
                <div class='header-top'>
                    <img src='/RTSS/resources/images/school-logo.png' class='logo' />
                    <div class='wrapper'>
                        <div class='statusBar'>
                            <div class='statusbar-item'>
                                <span class='statusbar'>
";

        /// To-Do: print user name

        echo
"                                </span>
                            </div>
                            <div class='statusbar-item'>
                                <a class='statusbar'>
                                    Log out
                                </a>
                            </div>
                            <div style='clear:both;'></div>
                        </div>
                        <div style='clear:both;'></div>

                        <div class='menubar'>
                            <div class='menu-foreground'>
";
        for ($i = 0; $i < count($mainMenu); $i++)
        {
            echo
"                                <div class='menu-item";
            if ($i == $mainIndex)
            {
                echo " active";
            }

            echo
"'>
                                    <a class='menu' href='";
            echo "$mainMenuLinks[$i]";
            echo
"'>
                                        <span class='menu'>
";

            echo $mainMenu[$i];
            echo
"
                                        </span>
                                    </a>
                                </div>
";
        }


        echo
        " <div style='clear:both;'></div>
                            </div>
                        </div>
                        <div style='clear:both;'></div>
                    </div>
                    <div style='clear:both;'></div>
                </div>
                <div style='clear:both;'></div>
";


        // submenu
        echo
"                <div class='submenu'>
";
        if ($mainIndex != 0)
        {
            echo
            "                    <div class='submenu-title'>
                        <h1 class='submenu-title'>
                            <a class='submenu-title'>";
            echo $mainMenu[$mainIndex];
            echo
"
                            </a>
                        </h1>
                    </div>
";
        }

        // submenu bar
        echo
        "                   <div class='submenubar'>";
        $thisSubMenu = $submenu[$mainIndex];
        for ($i = 0; $i < count($thisSubMenu); $i++)
        {
            echo
            "<div class='submenu-item";

            if ($i == 0)
            {
                echo " first";
            }
            echo "'>
                            <a class='submenu'>
                                <span class='submenu'>
                                    ";
            echo $thisSubMenu[$i];
            echo
"
                                </span>
                            </a>
                        </div>";
        }

        echo
        "                    </div>
                    <div style='clear:both;'></div>
                    <img class='submenu-separator' src='/RTSS/resources/images/line.png'/>
                </div>
            </div>
            <div style='clear:both;'></div>
            <div class='content'>
";
    }

    public static function printFooter()
    {
        echo
"            </div>
            <div class='footer'>
                <span>Copyright @ 2013 CHIJ St Nicholas Girl's School All rights reserved
                </span>
            </div>
        </div>
    </body>
</html>
";
    }

}
?>
