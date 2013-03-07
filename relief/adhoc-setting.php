<?php
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

Template::printHeaderAndDoValidation('Home', 
        array('relief.css', 'adhoc.css'), 
        array("teacher-detail.js", "accordion.js", 'relief.js'), 
        Template::HOME, "Adhoc", Template::SCHEDULE);
?>
<form class="main" name="schedule" action="" method="post">
    <div class="accordion colorbox blue">
        <span href="" class="icon-link"></span>
        <span class="box-title">
            Previous Schedule Result
        </span>
    </div>
    <div>
        <table class="hovered table-info">
            <thead>
                <tr class="teacher-thead">
                    <?php
                        $width=array('50%', '110px', '50%', '80px', '60px', '170px');
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
                    $allocationList=array(
                        '8800121'=>array(
                            'lesson'=>array(
                                array('class'=>array ('2P'), 'time'=>array(1, 2), 'lessonID'=>'N1313122PQ82'),
                                array('class'=>array ('2P 5P'), 'time'=>array(3, 6), 'lessonID'=>'N88')
                            ),
                            'reliefTeacher'=>'AED 1'
                        ),
                        '8800123'=>array(
                            'lesson'=>array(
                                array('class'=>array ('2Q'), 'time'=>array(9, 10), 'lessonID'=>'X11')
                            ),
                            'reliefTeacher'=>'Cool'
                        )
                    );                            
                    PageConstant::escapeHTMLEntity($allocationList);

                    $i=0;
                    foreach ($allocationList as $reliefAccname => $allocation)
                    {
                        $isFirstRow=true;
                        foreach ($allocation['lesson'] as $lesson)
                        {
                            $timeFrom=SchoolTime::getTimeValue($lesson['time'][0]);
                            $timeTo=SchoolTime::getTimeValue($lesson['time'][1]);

                            $timeFromOptionStr=PageConstant::formatOptionInSelect(SchoolTime::getTimeArrSub(0, -1), '', true);
                            $timeToOptionStr=PageConstant::formatOptionInSelect(SchoolTime::getTimeArrSub(1, 0), '', true);

                            $classStr=implode(', ', $lesson['class']);

                            $firstRowSpanName='';
                            $firstRowSpanPeriod='';
                            if ($isFirstRow)
                            {
                                $rowNum=count($allocation['lesson']);
                                $firstRowSpanName=<<< EOD
<td rowspan="$rowNum">{$allocation['reliefTeacher']}</td>
EOD;
                                $firstRowSpanPeriod=<<< EOD
<td rowspan="$rowNum">
    <select name="busy-from-$i"><option value="">--</option>$timeFromOptionStr</select>
    -    
    <select name="busy-to-$i"><option value="">--</option>$timeToOptionStr</select>
</td>
EOD;
                                $isFirstRow=false;
                            }

                            echo <<< EOD
<tr>$firstRowSpanName<td>$timeFrom - $timeTo</td><td>$classStr</td><td>{$lesson['reply']}</td>
    <td>
        <input type="checkbox" name="unavailable-$i" />
        <input type="hidden" name="relief-accname-$i" value="$reliefAccname" />
        <input type="hidden" name="lessonID-$i" value="{$lesson['lessonID']}" />
    </td>
    $firstRowSpanPeriod
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
        <input type="submit" value="Go" class="button" />
    </div>
    <div style="clear: both"></div>
</form>
<div id="dialog-alert"></div>
<?php
Template::printFooter();
?>
