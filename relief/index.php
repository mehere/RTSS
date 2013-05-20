<?php
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

Template::printHeaderAndDoValidation('Home', 
        array('relief.css'), 
        array("teacher-detail.js", "accordion.js", 'relief.js'), 
        Template::HOME, Template::HOME, Template::SCHEDULE);

$date=$_POST['date'];
if (!$date)
{
    $date=$_SESSION['scheduleDate'];
    if (!$date) $date=date(PageConstant::DATE_FORMAT_ISO);
}
$_SESSION['scheduleDate']=$date;
?>
<form class="main" name="schedule" action="schedule/_schedule.php" method="post">
    <div style="margin-bottom: 10px">
        Date: <input type="text" class="textfield datefield" name="date-display" maxlength="10" /><input type="hidden" name="date" value="<?php echo $date; ?>" /> <img id="calendar-trigger" src="/RTSS/img/calendar.gif" alt="Calendar" style="vertical-align: middle; cursor: pointer" />
    </div>
    <div class="accordion colorbox blue">
        <a href="" class="icon-link"><img src="/RTSS/img/minus-white.png" /><img src="/RTSS/img/plus-white.png" style="display: none" /></a>
        <span class="box-title">
            Leave Status
        </span>
        <?php if (new DateTime($date) > new DateTime('yesterday')) { ?>
            <div class="control-top"><a href="teacher-edit.php">Edit/Add</a></div>
        <?php } ?>
    </div>
    <div>
        <table class="hovered table-info">
            <thead>
                <tr class="teacher-thead">
                    <?php
                        // $width=array('30%', '80px', '170px', '70%', '80px', '100px');
                        $width=array('100%', '90px', '160px', '100px', '180px', '40px');
                        $tableHeaderList=array_values(NameMap::$RELIEF['teacherOnLeave']['display']);

                        for ($i=0; $i<count($tableHeaderList); $i++)
                        {
                            // class="sort"
                            echo <<< EOD
                                <th class="hovered" style="width: $width[$i]">$tableHeaderList[$i]<!--span class="ui-icon ui-icon-arrowthick-2-n-s"></span--></th>
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
                    $reasonArr=NameMap::$RELIEF['leaveReason']['display'];
                    
                    $isScheduleRun=false;

                    foreach ($teacherOnLeaveList as $teacher)
                    {
                        $datetime=$teacher[$keyList[2]];
                        $dateFromDisplay=SchoolTime::convertDate($datetime[0][0]);
                        $dateToDisplay=SchoolTime::convertDate($datetime[1][0]);

//                        $leaveID=$teacher[$keyExtraList[1]];
//                                    $verifiedStr=PageConstant::stateRepresent($teacherVerifiedList[$leaveID]);
                        $scheduledStr=PageConstant::stateRepresent($teacher[$keyList[5]]);
                        $isScheduleRun |= $teacher[$keyList[5]];
                        echo <<< EOD
<tr><td class="text-left"><a class="teacher-detail-link" href="_teacher_detail.php?accname={$teacher[$keyExtraList[0]]}">{$teacher[$keyList[0]]}</a></td><td>{$teacher[$keyList[1]]}</td><td>$dateFromDisplay, {$datetime[0][1]}<br />$dateToDisplay, {$datetime[1][1]}</td><td>{$teacher[$keyList[3]]}</td><td>{$reasonArr[$teacher[$keyList[4]]]}</td><td>$scheduledStr</td></tr>
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
    <div class='accordion colorbox green'>
        <a href="" class="icon-link"><img src="/RTSS/img/minus-white.png" /><img src="/RTSS/img/plus-white.png" style="display: none" /></a>
        <span class="box-title">
            Temporary Relief
        </span>
        <?php if (new DateTime($date) > new DateTime('yesterday')) { ?>
            <div class="control-top"><a href="teacher-edit.php?teacher=temp">Edit/Add</a></div>
        <?php } ?>
    </div>    
    <div>
        <table class="hovered table-info">
            <thead>
                <tr class="teacher-thead">
                    <?php
                        $width=array('30%', '100px', '130px', '70%');
                        $tableHeaderList=array_values(NameMap::$RELIEF['tempTeacher']['display']);

                        for ($i=0; $i<count($tableHeaderList); $i++)
                        {
                            // class="sort"
                            echo <<< EOD
                                <th class="hovered" style="width: $width[$i]">$tableHeaderList[$i]<!--span class="ui-icon ui-icon-arrowthick-2-n-s"></span--></th>
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
<tr><td class="text-left">{$teacher[$keyList[0]]}</td><td>{$teacher[$keyList[1]]}</td><td>{$datetime[0][1]} - {$datetime[1][1]}</td><td class="text-left">{$teacher[$keyList[3]]}</td></tr>
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
    <div class='accordion colorbox yellow'>
        <a href="" class="icon-link"><img src="/RTSS/img/minus-white.png" /><img src="/RTSS/img/plus-white.png" style="display: none" /></a>
        <span class="box-title">
            Excluding List
        </span>
        <?php if (new DateTime($date) > new DateTime('yesterday')) { ?>
            <div class="control-top"><a href="exclude-list.php">Edit/Add</a></div>
        <?php } ?>
    </div>    
    <div>
        <table class="hovered table-info">
            <?php
                $accList=Teacher::getExcludingList();

                $execInfo=Teacher::getTeacherInfo('executive');
                $nonexecInfo=Teacher::getTeacherInfo('non-executive');

                $adminList=array();
                $normalList=array();
                foreach ($accList as $value)
                {
                    if (isset($execInfo[$value]))
                    {
                        $adminList[]=$execInfo[$value]['fullname'];
                    }
                    if (isset($nonexecInfo[$value]))
                    {
                        $normalList[]=$nonexecInfo[$value]['fullname'];
                    }
                }
            ?>
            <tr><th style="width: 120px" class="hovered"><?php echo NameMap::$RELIEF['excludingList']['display']['executive']; ?></th><td><?php echo implode(', ', $adminList); ?></td></tr>
            <tr><th class="hovered"><?php echo NameMap::$RELIEF['excludingList']['display']['non-executive']; ?></th><td><?php echo implode(', ', $normalList); ?></td></tr>
        </table>
    </div>

    <div style="clear: both"></div>
    <div class="bt-control">
        <?php 
            if (new DateTime($date) > new DateTime('yesterday')) { 
                if ($isScheduleRun) {                    
        ?>
            <a href="" id="btnScheduleAll" class="button red"><img src="/RTSS/img/redo.png" class="icon" />Re-Schedule All</a>
            <a href="adhoc-setting.php" id="btnScheduleAdhoc" class="button"><img src="/RTSS/img/triangle.png" class="icon" />Schedule the Remaining</a>
        <?php } else { ?>
            <a href="" id="btnScheduleAll" class="button"><img src="/RTSS/img/triangle.png" class="icon" />Schedule All</a>
        <?php } 
            }
        ?>
    </div>
    <div style="clear: both"></div>
</form>
<div id="teacher-detail"></div>
<div id="dialog-alert"></div>
<form id="dialog-class" name="exclude-class" action="" method="post">
    <table class="hovered table-info">
        <thead>
            <tr class="teacher-thead">
                <?php
                    $width=array('50px', '130px', '110px', '130px', '100%');
                    $tableHeaderList=array_values(NameMap::$RELIEF['excludeClass']['display']);
                    array_unshift($tableHeaderList, '');

                    for ($i=0; $i<count($tableHeaderList); $i++)
                    {
                        // class="sort"
                        echo <<< EOD
                            <th class="hovered" style="width: $width[$i]">$tableHeaderList[$i]<!--span class="ui-icon ui-icon-arrowthick-2-n-s"></span--></th>
EOD;
                    }
                ?>
            </tr>
        </thead>
        <tbody >
            <?php
                $timeArr=SchoolTime::getTimeArrSub(0, -1);
                
                $classList=SchedulerDB::getLessonsOnLeave($date);
                PageConstant::escapeHTMLEntity($classList);
                
                $keyList=array_keys(NameMap::$RELIEF['excludeClass']['display']);
                $keyExtraList=NameMap::$RELIEF['excludeClass']['hidden'];
                
                foreach ($classList as $i => $classInfo)
                {
                    $startTime=$timeArr[$classInfo[$keyExtraList[2]]-1];
                    $endTime=$timeArr[$classInfo[$keyExtraList[3]]-1];
                    echo <<< EOD
<tr>
    <td>
        <input type="checkbox" name="class-select-$i" />
        <input type="hidden" name="type-$i" value="{$classInfo[$keyExtraList[1]]}" />
        <input type="hidden" name="teacher-accname-$i" value="{$classInfo[$keyExtraList[0]]}" />
        <input type="hidden" name="start-time-$i" value="$startTime" />
        <input type="hidden" name="end-time-$i" value="$endTime" />
    </td>
    <td class="text-left">{$classInfo[$keyList[0]]}</td><td>{$classInfo[$keyList[1]]}</td><td>$startTime - $endTime</td>
    <td><a class="teacher-detail-link" href="_teacher_detail.php?accname={$classInfo[$keyExtraList[0]]}">{$classInfo[$keyList[3]]}</a></td>
</tr>
EOD;
                }
                if (empty($classList))
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
    <input type="hidden" name="exclude-class-num" value="<?php echo count($width); ?>" />
</form>
<?php
    Template::printFooter();
    
    unset($_SESSION['scheduleType']);
    unset($_SESSION['scheduleIndex']);
?>
