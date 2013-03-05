<?php
spl_autoload_register(function($class){
    require_once "../../class/$class.php";
});

Template::printHeaderAndDoValidation('Schedule Result', 
        array('relief.css', 'page-control.css', 'schedule-result.css'),
        array('result.js'), 
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
        <span class="icon-link">&#x25CB;</span>
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
<tr><td>$classStr<input type="hidden" name="lessonID-$key" value="{$value['id']}" /></td>
<td>$timeStart<span style="margin: 0 3px">-</span>$timeEnd</td>        
<input type="hidden" name="time-start-$key" value="{$value['time'][0]}" />
<input type="hidden" name="time-end-$key" value="{$value['time'][1]}" />
</td>
<td>{$value['teacherOnLeave']}<input type="hidden" name="teacher-accname-$key" value="{$value['teacherAccName']}" /></td>
<td class="relief-col">
<span class="text-display">{$value['reliefTeacher']}</span>
<input type="text" name="relief-teacher-$key" value="{$value['reliefTeacher']}" class="text-hidden" />
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
        <div class="page-control">Schedule Result Choice</div>
        <div class="page-control">                    	
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
<a class="button" href="../">Go Back</a>   
EOD;
            }
            else 
            {
                echo <<< EOD
<a class="button green" href="timetable.php?schedule=$curPage">Preview Timetable</a>
<input type="submit" value="Approve" class="button" />
EOD;
            }
        ?>
    </div>
    <input type="hidden" name="num" value="<?php echo $scheduleResultNum; ?>" />
    <input type="hidden" name="schedule-index" value="<?php echo $curScheduleIndex; ?>" />
</form>
<div id="dialog-alert"><?php echo $_SESSION['scheduleError']; ?></div>
<?php
Template::printFooter();

unset($_SESSION['timetableAnalyzer']);
unset($_SESSION['abbrNameList']);
unset($_SESSION['scheduleError']);
?>