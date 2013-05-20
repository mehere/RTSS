<?php
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

Template::printHeaderAndDoValidation('Home', 
        array('relief.css', 'adhoc.css'), 
        array("teacher-detail.js", 'adhoc.js'), 
        Template::HOME, "Adhoc Scheduling", Template::SCHEDULE);
?>
<form class="main" name="schedule" method="post" action="schedule/_adhoc.php">
    <div class="accordion colorbox blue">        
        <span class="box-title">
            Previous Schedule Result
        </span>
    </div>
    <div>
        <table class="hovered table-info">
            <thead>
                <tr class="teacher-thead">
                    <?php
                        $width=array('50%', '110px', '50%', '90px', '60px', '170px');
                        $tableHeaderList=array_values(NameMap::$RELIEF_EDIT['adhocSchedule']['display']);

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
            <tbody>
                <?php                            
                    $allocationList=AdHocSchedulerDB::getApprovedSchedule($_SESSION['scheduleDate']);                          
                    PageConstant::escapeHTMLEntity($allocationList);

                    $i=0;
                    foreach ($allocationList as $reliefAccname => $allocation)
                    {
                        $isFirstRow=true;
                        foreach ($allocation['lesson'] as $lesson)
                        {
                            $timeFrom=SchoolTime::getTimeValue($lesson['time'][0]);
                            $timeTo=SchoolTime::getTimeValue($lesson['time'][1]);

                            $timeFromOptionStr=PageConstant::formatOptionInSelect(SchoolTime::getTimeArrSub(0, $lesson['time'][0]-1), $lesson['time'][0]-1);
                            $timeToOptionStr=PageConstant::formatOptionInSelect(SchoolTime::getTimeArrSub($lesson['time'][1]-1, -1, true));

                            $classStr=implode(', ', $lesson['class']);

                            $firstRowSpanName='';
                            if ($isFirstRow)
                            {
                                $rowNum=count($allocation['lesson']);
                                $firstRowSpanName=<<< EOD
<td rowspan="$rowNum"><a href="/RTSS2/relief/_teacher_detail.php?accname=$reliefAccname" class="teacher-detail-link">{$allocation['reliefTeacher']}</a></td>
EOD;

                                $isFirstRow=false;
                            }

                            echo <<< EOD
<tr>$firstRowSpanName<td>$timeFrom - $timeTo</td><td>$classStr</td><td>{$lesson['reply']}</td>
    <td>
        <input type="checkbox" name="unavailable-$i" />
        <input type="hidden" name="relief-accname-$i" value="$reliefAccname" />
        <input type="hidden" name="lessonID-$i" value="{$lesson['lessonID']}" />
        <input type="hidden" name="reliefID-$i" value="{$lesson['reliefID']}" />            
    </td>
    <td>
        <select name="busy-from-$i" disabled="disabled">$timeFromOptionStr</select>
        -    
        <select name="busy-to-$i" disabled="disabled">$timeToOptionStr</select>
    </td>
</tr>
EOD;
                            $i++;
                        }

                    }
                ?>                            
            </tbody>
        </table>
    </div>                
    <div class="bt-control">
        <input type="submit" name="go" value="Go" class="button" />
    </div>
    <div style="clear: both"></div>
    <input type="hidden" name="num" value="<?php echo $i; ?>" />
</form>
<div id="dialog-alert"></div>
<div id="teacher-detail"></div>
<?php
Template::printFooter();
?>
