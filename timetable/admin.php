<?php
include_once '../php-head.php';
include_once '../head-frag.php';

$timeFromArr=SchoolTime::getTimeArrSub(0, -1);
$timeToArr=SchoolTime::getTimeArrSub(1, 0);
?>
<title><?php echo PageConstant::SCH_NAME_ABBR . " " . PageConstant::PRODUCT_NAME; ?></title>
<link href="/RTSS/css/main.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/timetable.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/upload.css" rel="stylesheet" type="text/css">
<script src="/RTSS/js/upload.js"></script>

<link href="/RTSS/jquery-ui/ui-lightness/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css" />
<script src="/RTSS/jquery-ui/jquery-ui-1.9.2.custom.min.js"></script>

</head>
<body>

<div id="container">
    <div id="content-wrapper">
    	<div id="content">
            <?php
                $TOPBAR_LIST=array(
                    array('tabname'=>'Timetable', 'url'=>"/RTSS/timetable/admin.php"),
                    array('tabname'=>'Admin', 'url'=>""),
                );
                include '../topbar-frag.php';
            ?>
            <form class="main" name="timetable" action="_upload.php" method="post" enctype="multipart/form-data">
            	<h3>Upload Timetable</h3>
                <div class="line"><span class="label">Year:</span>
                	<select name="year">
                        <?php
                            $curYear=date('Y');
                            for ($i=$curYear-PageConstant::NUM_OF_YEAR; $i<=$curYear+PageConstant::NUM_OF_YEAR; $i++)
                            {
                                $selected=$i==$curYear ? 'selected="selected"' : '';
                                echo <<< EOD
                                    <option value="$i" $selected >$i</option>
EOD;
                            }
                        ?>
                    </select>
                </div>
                <div class="line"><span class="label">Semester:</span>
                	<select name="sem">
                    	<option value="1">1</option>
                    	<option value="2">2</option>
                    </select>
                </div>
                <div class="line"><span class="label">File:</span><input type="file" name="timetableFile" /></div>
                <?php
                if (isset($_SESSION['uploadError'])){
                    $msg = $_SESSION['uploadError'];
                    echo "<div class=\"error-msg\"> $msg</div>";
                }
                ?>
                <div class="line"><span class="label">&nbsp;</span><input type="submit" value="Upload" name="submit" style="font-size: .9em" class="button" /></div>
            </form>
            <hr style="margin: 0 30px" />
            <div class="main">
            	<h3 style="margin-bottom: 0; margin-top: 10px">Add AED Timetable</h3>
                <form name="add-class">
                    <table class="form-table">
                    	<thead>
                            <tr>
                                <?php
                                    $width=array("55px", "20%", "55px", "40%", "65px", "40%", "70px");
                                    foreach ($width as $value)
                                    {
                                        echo <<< EOD
<td style="width: $value"></td>
EOD;
                                    }
                                ?>
                            </tr>
                        </thead>
                    	<tr>
                            <td class="label">Day:</td>
                            <td>
                                <select name="day">
                                    <?php
                                        $dayArr=PageConstant::$DAY;
                                        for ($i=0; $i<count($dayArr); $i++)
                                        {
                                            echo <<< EOD
                                                <option value="$i">{$dayArr[$i]}</option>
EOD;
                                        }
                                    ?>
                                </select>
                            </td>
                            <td class="label">Time:</td>
                            <td>
                                <select name="time-from" style="width: 65px">
                                    <?php
                                        echo PageConstant::formatOptionInSelect($timeFromArr, '');
                                    ?>
                                </select>
                                <select name="time-to" style="margin-left: 10px; width: 65px">
                                    <?php
                                        echo PageConstant::formatOptionInSelect($timeToArr, '');
                                    ?>
                                </select>
                            </td>
                            <td class="label">Subject:</td>
                            <td><input type="text" name="subject" style="width: 90%" /></td>
                            <td></td>
                    	</tr>
                        <tr>
                        	<td class="label">Venue:</td>
                            <td><input type="text" name="venue" class="text-field" style="width: 100%" /></td>
                            <td class="label">Class:</td>
                            <td colspan="3"><input type="text" name="class" class="text-field" style="width: 50%" /> <span class="comment">Use <strong class="punc">;</strong> or <strong class="punc">,</strong> to separate classes</span></td>
                            <td><input type="submit" class="button" value="Add" style="font-size: 14px" /></td>
                        </tr>
                    </table>
                </form>
                <form name="AED" style="position: relative" method="post" action="_timetable.php">
                    <table class="table-info">
                        <thead>
                            <th style="width: 90px"></th>
                            <?php
                                foreach($dayArr as $day)
                                {
                                    echo <<< EOD
                                        <th style="width: 20%">$day</th>
EOD;
                                }
                            ?>
                        </thead>
                        <tbody>
                            <?php
                                for ($i=0; $i<count($timeFromArr); $i++)
                                {
                                    // Debug: <td>{$timeArr[$i]} Mon</td><td>{$timeArr[$i]} Tue</td><td>{$timeArr[$i]} Wed</td><td>{$timeArr[$i]} Thu</td><td>{$timeArr[$i]} Fri</td>
                                    echo <<< EOD
<tr><td class="time-col">{$timeFromArr[$i]}<span style="margin: 0 3px">-</span>{$timeToArr[$i]}</td><td></td><td></td><td></td><td></td><td></td></tr>
EOD;
                                }
                            ?>
                        </tbody>
                    </table>
                    <div class="row">
                    	<span class="label">AED Name:</span><input type="text" name="fullname" /><input type="hidden" name="accname" />
                        <span class="label">Year:</span>
                        <select name="year">
                            <?php
                                $curYear=date('Y');
                                for ($i=$curYear-PageConstant::NUM_OF_YEAR; $i<=$curYear+PageConstant::NUM_OF_YEAR; $i++)
                                {
                                    $selected=$i==$curYear ? 'selected="selected"' : '';
                                    echo <<< EOD
                                        <option value="$i" $selected >$i</option>
EOD;
                                }
                            ?>
                        </select>
                        <span class="label">Sem:</span>
                        <select name="sem">
                            <option value="1">1</option>
                            <option value="2">2</option>
                        </select>
                        <input type="submit" class="button" value="Submit" style="margin-left: 30px" />
                    </div>
                </form>
            </div>
            <div id="dialog-alert"></div>
        </div>
    </div>
    <?php
        include '../sidebar-frag.php';
        unset($_SESSION['uploadError']);
    ?>
</div>

</body>
</html>
