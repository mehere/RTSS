<?php   
include_once '../php-head.php';
include_once '../head-frag.php';    
?>
<title><?php echo PageConstant::SCH_NAME_ABBR . " " . PageConstant::PRODUCT_NAME; ?></title>
<link href="/RTSS/css/main.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/relief.css" rel="stylesheet" type="text/css" />
<script src="/RTSS/js/relief.js"></script>
<script src="/RTSS/js/teacher-detail.js"></script>

<link href="/RTSS/jquery-ui/ui-lightness/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css" />
<script src="/RTSS/jquery-ui/jquery-ui-1.9.2.custom.min.js"></script>

<style type="text/css">
#align-teacher tr td:nth-child(1), #align-teacher tr td:nth-child(4) {
	text-align: left;
	word-wrap: break-word;
}
#align-temp tr td:nth-child(1), #align-temp tr td:nth-child(4) {
	text-align: left;
	word-wrap: break-word;
}
</style>
</head>
<body>

<div id="container">
    <div id="content-wrapper">
    	<div id="content">
            <?php 
                $TOPBAR_LIST=array(
                    array('tabname'=>'Scheduling', 'url'=>"/RTSS/relief/"), 
                    array('tabname'=>'Start', 'url'=>""), 
                );
                include '../topbar-frag.php';
                
                require_once '../class/Teacher.php';
                $date=$_POST['date'];
                if (!$date)
                {
                    $date=$_SESSION['scheduleDate'];
                    if (!$date) $date=date('Y-m-d');
                }
                $_SESSION['scheduleDate']=$date;
                
                // Teacher verified
                $teacherVerifiedList=$_SESSION['teacherVerified'];
                $teacherScheduledList=$_SESSION['teacherScheduled'];
            ?>
            <form class="main" name="schedule" action="schedule/" method="post">
            	Date: <input type="text" class="textfield" name="date" maxlength="10" value="<?php echo $date; ?>" /> <img id="calendar-trigger" src="/RTSS/img/calendar.gif" alt="Calendar" style="vertical-align: middle; cursor: pointer" />
                <div class="section">
                	Teacher on Leave: <a href="teacher-edit.php">Edit/Add</a>
                    <table class="table-info">
                        <thead>
                            <tr class="teacher-thead">
                                <?php                                 
                                    $width=array('30%', '80px', '170px', '70%', '80px', '100px');                                                                        
                                    $tableHeaderList=array_values(NameMap::$RELIEF['teacherOnLeave']['display']);
                                    
                                    for ($i=0; $i<count($tableHeaderList); $i++)
                                    {
                                        echo <<< EOD
                                            <th style="width: $width[$i]" class="sort">$tableHeaderList[$i]<!--span class="ui-icon ui-icon-arrowthick-2-n-s"></span--></th>
EOD;
                                    }
                                ?>
                            </tr>
                        </thead>
                        <tbody id="align-teacher">
                            <?php 
                                $teacherOnLeaveList=Teacher::getTeacherOnLeave($date);
                                PageConstant::escapeHTMLEntity($teacherOnLeaveList);
                                $keyList=array_keys(NameMap::$RELIEF['teacherOnLeave']['display']);
                                $keyExtraList=NameMap::$RELIEF['teacherOnLeave']['hidden'];
                                foreach ($teacherOnLeaveList as $teacher) 
                                {                                    
                                    $datetime=$teacher[$keyList[2]];
                                    $leaveID=$teacher[$keyExtraList[1]];
                                    $verifiedStr=PageConstant::stateRepresent($teacherVerifiedList[$leaveID]);
                                    $scheduledStr=PageConstant::stateRepresent($teacherScheduledList[$leaveID]);
                                    echo <<< EOD
<tr><td><a class="teacher-detail-link" href="_teacher_detail.php?accname={$teacher[$keyExtraList[0]]}">{$teacher[$keyList[0]]}</a></td><td>{$teacher[$keyList[1]]}</td><td>{$datetime[0][0]} {$datetime[0][1]}<br />{$datetime[1][0]} {$datetime[1][1]}</td><td>{$teacher[$keyList[3]]}</td><td>$verifiedStr</td><td>$scheduledStr</td></tr>   
EOD;
                                }
                                if (empty($teacherOnLeaveList))
                                {
                                    echo "<tr>";
                                    foreach ($width as $value)
                                    {
                                        echo "<td>--</td>";
                                    }
                                    echo "</tr>";
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="section">
                	Temporary Relief Teacher: <a href="teacher-edit.php?teacher=temp">Edit/Add</a>
                    <table class="table-info">
                        <thead>
                            <tr class="teacher-thead">
                                <?php                                 
                                    $width=array('30%', '100px', '130px', '70%');                                                                        
                                    $tableHeaderList=array_values(NameMap::$RELIEF['tempTeacher']['display']);
                                    
                                    for ($i=0; $i<count($tableHeaderList); $i++)
                                    {
                                        echo <<< EOD
                                            <th style="width: $width[$i]" class="sort">$tableHeaderList[$i]<!--span class="ui-icon ui-icon-arrowthick-2-n-s"></span--></th>
EOD;
                                    }
                                ?>                               
                            </tr>
                        </thead>
                        <tbody id="align-temp">
                            <?php 
                                $tempTeacherList=Teacher::getTempTeacher($date);
                                PageConstant::escapeHTMLEntity($tempTeacherList);
                                $keyList=array_keys(NameMap::$RELIEF['tempTeacher']['display']);
                                $keyExtraList=NameMap::$RELIEF['tempTeacher']['hidden'];
                                foreach ($tempTeacherList as $teacher) 
                                {
                                    $datetime=$teacher[$keyList[2]];
                                    echo <<< EOD
<tr><td>{$teacher[$keyList[0]]}</td><td>{$teacher[$keyList[1]]}</td><td>{$datetime[0][1]} - {$datetime[1][1]}</td><td>{$teacher[$keyList[3]]}</td></tr>
EOD;
                                }
                                if (empty($tempTeacherList))
                                {
                                    echo "<tr>";
                                    foreach ($width as $value)
                                    {
                                        echo "<td>--</td>";
                                    }
                                    echo "</tr>";
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="bt-control">
                	<input type="submit" value="Schedule All" class="button" />
                    <input type="submit" value="Adhoc Schedule" class="button" />
                </div>                
            </form>
            <div id="teacher-detail">Loading ...</div>
        </div>        
    </div>
    <?php include '../sidebar-frag.php'; ?>
</div>
    
</body>
</html>
