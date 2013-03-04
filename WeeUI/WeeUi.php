<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
        <meta http-equiv="Pragma" content="no-cache" />
        <meta http-equiv="Expires" content="0" />
        <title></title>
        <link rel="stylesheet" type="text/css" href="weeUi.css"/>
        <link rel="stylesheet" type="text/css" href="/RTSS/resources/lib/jquery/css/flat/jquery-ui-1.10.1.custom.css" rel="Stylesheet" />
        <script src="/RTSS/resources/lib/jquery/js/jquery-1.9.1.js"></script>
        <script src="/RTSS/resources/lib/jquery/js/jquery-ui-1.10.1.custom.js"></script>
        <script>

            jQuery(document).ready(function(){
                // accordion code
                $('.accordion').click(function() {
                    $(this).next().toggle('normal');
                    var status = $(this).children('.status');
                    status.children('.active').toggle();
                    status.children('.inactive').toggle();
                    return false
                });

                // date picker
                $( "#datepicker" ).datepicker({
                    showOn: "button",
                    buttonImage: "/RTSS/resources/images/calendar.gif",
                    buttonImageOnly: true
                });

            });
        </script>
    </head>
    <body>
        <div class="container">
            <!--            <div class="background">
                            <img src="/RTSS/WeeUI/page_gradient_linear.png" class="stretch" alt="Background" />
                        </div>-->
            <div class="header">
                <div class="header-top">
                    <img src="/RTSS/resources/images/school-logo.png" class="logo" />
                    <div class="wrapper">
                        <div class="statusBar">
                            <div class="statusbar-item">
                                <span class="statusbar">
                                    User
                                </span>
                            </div>
                            <div class="statusbar-item">
                                <a class="statusbar">
                                    Log out
                                </a>
                            </div>
                            <div style="clear:both;"></div>
                        </div>
                        <div style="clear:both;"></div>

                        <div class="menubar">
                            <div class="menu-foreground">

                                <div class="menu-item">
                                    <a class="menu">
                                        <span class="menu">
                                            <img class="menu" src="/RTSS/resources/images/home.png"/>
                                        </span>
                                    </a>
                                </div>
                                <div class="menu-item active">
                                    <a class="menu">
                                        <span class="menu">
                                            Upload Timetable
                                        </span>
                                    </a>
                                </div>
                                <div class="menu-item">
                                    <a class="menu">
                                        <span class="menu">
                                            View Records
                                        </span>
                                    </a>
                                </div>
                                <div class="menu-item lastchild">
                                    <a class="menu">
                                        <span class="menu">
                                            Menu 4
                                        </span>
                                    </a>
                                </div>
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

                    <img class="submenu-separator" src="/RTSS/resources/images/line.png"/>
                </div>
            </div>
            <div style="clear:both;"></div>
            <div class="content">

                <p>Date: <input type="text" id="datepicker" /></p>

                <div class="accordion colorbox blue">
                    <div class="status">
                        <img class="inactive" src="/RTSS/resources/images/plus-white.png"/>
                        <img class="active" src="/RTSS/resources/images/minus-white.png"/>
                    </div>

                    <span>
                        Leave Status
                    </span>
                </div>
                <div>
                    <table class="hovered">
                        <thead>
                            <tr>
                                <th class="hovered">Name</th>
                                <th class="hovered right">Time CP</th>
                                <th class="hovered right">Network</th>
                                <th class="hovered right">Traffic</th>
                                <th class="hovered right last">Tiles update</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr><td>Bing</td><td class="right">0:00:01</td><td class="right">0,1 Mb</td><td class="right">0 Mb</td><td class="right last">0,1 Mb</td></tr>
                            <tr><td>Internet Explorer</td><td class="right">0:00:01</td><td class="right">0,1 Mb</td><td class="right">0 Mb</td><td class="right last">0,1 Mb</td></tr>
                            <tr><td>Chrome</td><td class="right">0:00:01</td><td class="right">0,1 Mb</td><td class="right">0 Mb</td><td class="right last">0,1 Mb</td></tr>
                            <tr><td>News</td><td class="right">0:00:01</td><td class="right">0,1 Mb</td><td class="right">0 Mb</td><td class="right last">0,1 Mb</td></tr>
                            <tr><td>Weather</td><td class="right">0:00:01</td><td class="right">0,1 Mb</td><td class="right">0 Mb</td><td class="right last">0,1 Mb</td></tr>
                            <tr><td>Music</td><td class="right">0:00:01</td><td class="right">0,1 Mb</td><td class="right">0 Mb</td><td class="right last">0,1 Mb</td></tr>
                        </tbody>

                        <tfoot></tfoot>
                    </table>
                </div>
                <div class="accordion colorbox green">
                    <div class="status">
                        <img class="inactive" src="/RTSS/resources/images/plus-white.png"/>
                        <img class="active" src="/RTSS/resources/images/minus-white.png"/>
                    </div>

                    <span>
                        Leave Status
                    </span>
                </div>
                <div>
                    <table class="hovered">
                        <thead>
                            <tr>
                                <th class="hovered">Name</th>
                                <th class="hovered right">Time CP</th>
                                <th class="hovered right">Network</th>
                                <th class="hovered right">Traffic</th>
                                <th class="hovered right last">Tiles update</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr><td>Bing</td><td class="right">0:00:01</td><td class="right">0,1 Mb</td><td class="right">0 Mb</td><td class="right last">0,1 Mb</td></tr>
                            <tr><td>Internet Explorer</td><td class="right">0:00:01</td><td class="right">0,1 Mb</td><td class="right">0 Mb</td><td class="right last">0,1 Mb</td></tr>
                            <tr><td>Chrome</td><td class="right">0:00:01</td><td class="right">0,1 Mb</td><td class="right">0 Mb</td><td class="right last">0,1 Mb</td></tr>
                            <tr><td>News</td><td class="right">0:00:01</td><td class="right">0,1 Mb</td><td class="right">0 Mb</td><td class="right last">0,1 Mb</td></tr>
                            <tr><td>Weather</td><td class="right">0:00:01</td><td class="right">0,1 Mb</td><td class="right">0 Mb</td><td class="right last">0,1 Mb</td></tr>
                            <tr><td>Music</td><td class="right">0:00:01</td><td class="right">0,1 Mb</td><td class="right">0 Mb</td><td class="right last">0,1 Mb</td></tr>
                        </tbody>

                        <tfoot></tfoot>
                    </table>
                </div>
                <div class="accordion colorbox yellow">
                    <div class="status">
                        <img class="inactive" src="/RTSS/resources/images/plus-white.png"/>
                        <img class="active" src="/RTSS/resources/images/minus-white.png"/>
                    </div>

                    <span>
                        Leave Status
                    </span>
                </div>
                <div>
                    <table class="hovered">
                        <thead>
                            <tr>
                                <th class="hovered">Name</th>
                                <th class="hovered right">Time CP</th>
                                <th class="hovered right">Network</th>
                                <th class="hovered right">Traffic</th>
                                <th class="hovered right last">Tiles update</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr><td>Bing</td><td class="right">0:00:01</td><td class="right">0,1 Mb</td><td class="right">0 Mb</td><td class="right last">0,1 Mb</td></tr>
                            <tr><td>Internet Explorer</td><td class="right">0:00:01</td><td class="right">0,1 Mb</td><td class="right">0 Mb</td><td class="right last">0,1 Mb</td></tr>
                            <tr><td>Chrome</td><td class="right">0:00:01</td><td class="right">0,1 Mb</td><td class="right">0 Mb</td><td class="right last">0,1 Mb</td></tr>
                            <tr><td>News</td><td class="right">0:00:01</td><td class="right">0,1 Mb</td><td class="right">0 Mb</td><td class="right last">0,1 Mb</td></tr>
                            <tr><td>Weather</td><td class="right">0:00:01</td><td class="right">0,1 Mb</td><td class="right">0 Mb</td><td class="right last">0,1 Mb</td></tr>
                            <tr><td>Music</td><td class="right">0:00:01</td><td class="right">0,1 Mb</td><td class="right">0 Mb</td><td class="right last">0,1 Mb</td></tr>
                        </tbody>

                        <tfoot></tfoot>
                    </table>
                </div>

                <div style="clear:both;"></div>
                <div class="buttons">
                    <button class="command red"><div class="icon"><img src="/RTSS/resources/images/redo.png"/></div><div class="words"><span>Re-Schedule All<br><small>The previous relief plan will be cancelled</small></span></div></button>
                    <button class="command blue"><div class="icon"><img src="/RTSS/resources/images/triangle.png"/></div><div class="words-single"><span>Schedule the remaining</span></div></button>

                </div>
                <div style="clear:both;"></div>
            </div>
            <div class="footer">
                <span>Copyright @ 2013 CHIJ St Nicholas Girl's School All rights reserved
                </span>
            </div>
        </div>
    </body>
</html>
