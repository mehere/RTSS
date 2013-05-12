<?php
spl_autoload_register(function($class){
    require_once "../../class/$class.php";
});

Template::printHeaderAndDoValidation('Schedule Result', 
        array('relief.css', 'page-control.css', 'schedule-result.css'),
        array("teacher-detail.js", 'result.js'), 
        Template::HOME, Template::HOME . " (Result)", Template::SCHEDULE);

$scheduleIndexArr=$_SESSION['scheduleIndex'];
if (!$scheduleIndexArr) 
{
    $scheduleIndexArr=$_SESSION['scheduleIndex']=SchedulerDB::allSchduleIndex();
}
$scheduleResultNum=count($scheduleIndexArr);
$scheduleList=array();

$curPage=$_GET['result'];
if (!$curPage) $curPage=1;

$curScheduleIndex=$scheduleIndexArr[$curPage-1];
if ($scheduleIndexArr)
{
    $scheduleList=SchedulerDB::getScheduleResult($curScheduleIndex);
    PageConstant::escapeHTMLEntity($scheduleList);
}
?>
<form class="main" name="edit" action="_approve.php" method="post">
    <div class="accordion colorbox blue">        
        <span class="box-title">
            Result Preview
        </span>
        <?php if ($scheduleList) { ?>
            <div class="control-top">
                <a href="" id="override">Override</a>
                <a href="" id="override-ok" style="display: none">OK</a>
                <a href="" id="override-cancel" style="display: none">Cancel</a>
            </div>        
        <?php } ?>
    </div>
    <div class="section">        
        <table class="hovered table-info">
            <thead>
                <tr>
                    <?php
                        $width=array('24%', '130px', '38%', '38%');                                                                        

                        $tableHeaderList=array_values(NameMap::$SCHEDULE_RESULT['schedule']['display']);

                        for ($i=0; $i<count($tableHeaderList); $i++)
                        {
                            echo <<< EOD
                                <th class="hovered" style="width: $width[$i]">$tableHeaderList[$i]</th>
EOD;
                        }
                    ?>                                
                </tr>
            </thead>
            <tbody>
                <?php
                    

                    if ($scheduleList)
                    {
                        foreach ($scheduleList[$curScheduleIndex] as $key => $value)
                        {
                            $classStr=implode(', ', $value['class']);
                            $timeStart=SchoolTime::getTimeValue($value['time'][0]);
                            $timeEnd=SchoolTime::getTimeValue($value['time'][1]);
                            echo <<< EOD
<tr><td>$classStr<input type="hidden" name="lessonID-$key" value="{$value['id']}" /><input type="hidden" name="reliefID-$key" value="{$value['reliefID']}" /></td>
<td>$timeStart<span style="margin: 0 3px">-</span>$timeEnd</td>        
<input type="hidden" name="time-start-$key" value="{$value['time'][0]}" />
<input type="hidden" name="time-end-$key" value="{$value['time'][1]}" />
</td>
<td><a href="../_teacher_detail.php?accname={$value['teacherAccName']}" class="teacher-detail-link" >{$value['teacherOnLeave']}</a><input type="hidden" name="teacher-accname-$key" value="{$value['teacherAccName']}" /></td>
<td class="relief-col">
<span class="text-display"><a href="../_teacher_detail.php?accname={$value['reliefAccName']}" class="teacher-detail-link" >{$value['reliefTeacher']}</a></span>
<input type="text" name="relief-teacher-$key" value="{$value['reliefTeacher']}" class="text-hidden" /><input type="text" style="width: 0; opacity: 0; filter: alpha(opacity=0);" />
<input type="hidden" name="relief-accname-$key" value="{$value['reliefAccName']}" />
</td>
</tr>
EOD;
                        }
                    }
                    else
                    {
                        $scheduleResultNum=1;

                        $otherTdStr=implode('', array_map(array("PageConstant", "tdWrap"), array_fill(0, count($tableHeaderList), '--')));                                            
                        echo "<tr>$otherTdStr</tr>";
                    }
                ?>
            </tbody>
        </table>
        <div class="page-control">Schedule Result Choice <?php echo $curPage; ?></div>
        <div class="page-control" id="page-turn-wrapper">                    	
            <?php
                $prevPage=max(1, $curPage-1);
                echo <<< EOD
<a href="?result=$prevPage" class="page-no page-turn">&lt;</a>   
EOD;

                for ($i=1; $i<=$scheduleResultNum; $i++)
                {
                    $selectedStr='';
                    if ($curPage == $i) $selectedStr='page-selected';
                    echo <<< EOD
<a href="?result=$i" class="page-no $selectedStr">$i</a>
EOD;
                }

                $nextPage=min($scheduleResultNum, $curPage+1);
                echo <<< EOD
<a href="?result=$nextPage" class="page-no page-turn">&gt;</a>
EOD;
            ?>
        </div>
    </div>
    <div class="bt-control">
        <?php 
            if (!$scheduleList) 
            {
                echo <<< EOD
<a class="button" id="goback" href="../">Go Back</a>   
EOD;
            }
            else 
            {
                echo <<< EOD
<a class="button green" href="timetable.php?schedule=$curPage">Preview Timetable</a>
<input type="submit" name="approve" value="Approve" class="button" />
EOD;
            }
        ?>
    </div>
    <div style="clear: both"></div>
    <input type="hidden" name="num" value="<?php echo $scheduleResultNum; ?>" />
    <input type="hidden" name="schedule-index" value="<?php echo $curScheduleIndex; ?>" />
</form>
<div id="dialog-alert"><?php echo $_SESSION['scheduleError']; ?></div>
<div id="teacher-detail"></div>
<?php
Template::printFooter();

unset($_SESSION['timetableAnalyzer']);
unset($_SESSION['abbrNameList']);
unset($_SESSION['scheduleError']);
?>