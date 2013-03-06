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
        Date: <input type="text" class="textfield" name="date-display" maxlength="10" style="width: 6.5em" /><input type="hidden" name="date" value="<?php echo $date; ?>" /> <img id="calendar-trigger" src="/RTSS/img/calendar.gif" alt="Calendar" style="vertical-align: middle; cursor: pointer" />
    </div>
    <div class="accordion colorbox blue">
        <a href="" class="icon-link"><img src="/RTSS/img/minus-white.png" /><img src="/RTSS/img/plus-white.png" style="display: none" /></a>
        <span class="box-title">
            Leave Status
        </span>
        <div class="control-top"><a href="teacher-edit.php">Edit/Add</a></div>
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

                    foreach ($teacherOnLeaveList as $teacher)
                    {
                        $datetime=$teacher[$keyList[2]];
                        $dateFromDisplay=SchoolTime::convertDate($datetime[0][0]);
                        $dateToDisplay=SchoolTime::convertDate($datetime[1][0]);

                        $leaveID=$teacher[$keyExtraList[1]];
//                                    $verifiedStr=PageConstant::stateRepresent($teacherVerifiedList[$leaveID]);
                        $scheduledStr=PageConstant::stateRepresent($teacher[$keyList[5]]);
                        echo <<< EOD
<tr><td class="text-left"><a class="teacher-detail-link" href="_teacher_detail.php?accname={$teacher[$keyExtraList[0]]}">{$teacher[$keyList[0]]}</a></td><td>{$teacher[$keyList[1]]}</td><td>$dateFromDisplay, {$datetime[0][1]}<br />$dateToDisplay, {$datetime[1][1]}</td><td>{$teacher[$keyList[3]]}</td><td class="text-left">{$reasonArr[$teacher[$keyList[4]]]}</td><td>$scheduledStr</td></tr>
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
        <div class="control-top"><a href="teacher-edit.php?teacher=temp">Edit/Add</a></div>
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
        <div class="control-top"><a href="exclude-list.php">Edit/Add</a></div>        
    </div>    
    <div>
        <table class="hovered table-info">
            <?php
                $accList=Teacher::getExcludingList($date);

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
        <a href="" id="btnScheduleAll" class="button red"><img src="/RTSS/img/redo.png" class="icon" />Re-Schedule All</a>
        <a href="adhoc-setting.php" id="btnScheduleAdhoc" class="button"><img src="/RTSS/img/triangle.png" class="icon" />Schedule the Remaining</a>
    </div>
</form>
<div id="teacher-detail"></div>
<div id="dialog-alert"></div>
<?php
Template::printFooter();
?>        
