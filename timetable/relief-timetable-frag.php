<p>
    <span style="color: red;">Relief classes are highlighted in red.</span> <span style="color: blue;">For AED: classes highlighted in blue are not mandatory.</span>
</p>
<form name="teacher-select" class="accordion colorbox blue" method="post">
    <a href="" class="icon-link"><img src="/RTSS/img/minus-white.png" /><img src="/RTSS/img/plus-white.png" style="display: none" /></a>
    <span class="box-title">
        Individual Timetable
        <?php if ($isAdmin) {  #define $isAdmin & $teacherList ?>                                
            <select name="accname" class="dropdown">
                <option value="">-- Select a Teacher --</option>
                <?php echo $teacherList; ?>
            </select>
        <?php } ?>
    </span>
    <?php if ($NO_PREIVEW) { ?>
        <div class="control-top"><a href="" id="print-individual" target="_blank">Print</a></div>
    <?php } ?>
    <input type="hidden" name="date" />
</form>
<div>
    <table class="hovered table-info" style="width: 70%">
        <thead>
            <tr>
                <?php
                $width=array('120px', '30%', '40%', '30%');

                $headerKeyList=NameMap::$TIMETABLE['individual']['display'];
                $tableHeaderList=array_values($headerKeyList);

                for ($i=0; $i < count($tableHeaderList); $i++)
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
            // Define $timetableIndividual in the calling function
            $timeArr=SchoolTime::getTimeArrSub(0, -1);
            for ($i=0; $i < count($timeArr) - 1; $i++)
            {
                $teaching=$timetableIndividual[$i];

                if ($teaching)
                {
                    PageConstant::escapeHTMLEntity($teaching);
                    $teaching['class']=implode(", ", $teaching['class']);
                    if ($teaching['skipped'])
                    {
                        $teaching['skipped']['class']=implode(", ", $teaching['skipped']['class']);
                    }                

                    $style='';
                    switch ($teaching['attr'])
                    {
                        case -1:
                            $style='style="text-decoration: line-through"';
                            break;
                        case 1:
                            $style='style="color: blue"';
                            break;
                        case 2:
                            $style='style="color: red"';
                            break;
                    }

                    $timetableEntry=array();
                    foreach (array_slice($headerKeyList, 1) as $key => $value)
                    {                    
                        $skippedPart=$teaching['skipped'][$key];
                        if ($skippedPart)
                        {                       
                            $skippedPart= <<< EOD
    <div style="color: black;">(<span style="text-decoration: line-through;">$skippedPart</span>)</div>   
EOD;
                        }
                        $timetableEntry[]= <<< EOD
    <span $style>{$teaching[$key]}{$skippedPart}</span>
EOD;
                    }

                    $otherTdStr=implode('', array_map(array("PageConstant", "tdWrap"), $timetableEntry));
                    echo <<< EOD
    <tr><td class="time-col">{$timeArr[$i]}<span style="margin: 0 3px">-</span>{$timeArr[$i + 1]}</td>$otherTdStr</tr>
EOD;
                }
                else
                {
                    $otherTdStr=implode('', array_map(array("PageConstant", "tdWrap"), array_fill(0, count(NameMap::$TIMETABLE['individual']['display'])-1, '')));
                    echo <<< EOD
    <tr><td class="time-col">{$timeArr[$i]}<span style="margin: 0 3px">-</span>{$timeArr[$i + 1]}</td>$otherTdStr</tr>
EOD;
                }
            }
            ?>
        </tbody>
    </table>
</div>

<?php 
if ($_SESSION['type'] == 'admin' || $_SESSION['type'] == 'super_admin') 
{ 
    $viewOrder=$_POST['view-order'];
?>
<form name="relief-timetable" method="post" class='accordion colorbox green'>
    <a href="" class="icon-link"><img src="/RTSS/img/minus-white.png" /><img src="/RTSS/img/plus-white.png" style="display: none" /></a>
    <span class="box-title">
        Relief Timetable
    </span>
    <select name="view-order" class="dropdown">
        <option value="time" <?php if ($viewOrder == 'time') echo 'selected="selected"' ?>>View by Time</option>
        <option value="class" <?php if ($viewOrder == 'class') echo 'selected="selected"' ?>>View by Class</option>        
    </select>
    <?php if ($NO_PREIVEW) { ?>
        <div class="control-top"><a href="" id="print-relief" target="_blank">Print</a></div>
    <?php } ?>
    <input type="hidden" name="relief-timetable-order" value="<?php echo $_POST['relief-timetable-order'] ?>" />
    <input type="hidden" name="relief-timetable-direction" value="<?php echo $_POST['relief-timetable-direction'] ?>" />
</form>
<div>
    <table id="relief-timetable" class="hovered table-info">
        <thead>
            <tr>
                <?php
                $orderByClass=$viewOrder == 'class';
                
                $width=array('120px', '15%', '20%', '15%', '25%', '25%');

                $headerKeyList=NameMap::$TIMETABLE[$orderByClass ? 'layout2' : 'layout']['display'];

                $i=0;
                foreach ($headerKeyList as $key => $value)
                {                    
                    $dir='';
                    if ($_POST['relief-timetable-order'] == $key)
                    {
                        $dir=$_POST['relief-timetable-direction'];
                    }
                    echo <<< EOD
                        <th class="hovered" style="width: $width[$i]" search="$key" direction="$dir">$value<!--span class="ui-icon ui-icon-arrowthick-2-n-s"></span--></th>
EOD;
                    $i++;
                }
                ?>
            </tr>
        </thead>
        <tbody>
            <?php
            // Define $timetable in the calling function
            if ($orderByClass)
            {
                $timetable=TimetableDB::getReliefTimetableByClass($date); 
                PageConstant::escapeHTMLEntity($timetable);
            }
            else
            {
                $timetable=TimetableDB::getReliefTimetable('', '', $date, $curScheduleIndex?$curScheduleIndex:-1); 
                PageConstant::escapeHTMLEntity($timetable);
            }            

            $timeArr=SchoolTime::getTimeArrSub(0, -1);
            if ($orderByClass)
            {
                foreach ($timetable as $className => $teachingList)
                {                  
                    foreach ($teachingList as $tInd => $teaching)
                    {
                        PageConstant::escapeHTMLEntity($teaching);
                        $timetableEntry=array();
                        foreach (array_slice($headerKeyList, 1) as $key => $value)
                        {
                            $timetableEntry[$key]=$teaching[$key];
                        }

                        // Class name display/Account name                        
                        $timetableEntry['time']= $timeArr[$teaching['time-from']] . " - " . $timeArr[$teaching['time-to']];
                        
                        $timetableEntry['teacher-fullname']=<<< EOD
<a href="/RTSS/relief/_teacher_detail.php?accname={$teaching['teacher-accname']}" class="teacher-detail-link">{$timetableEntry['teacher-fullname']}</a>
EOD;
                        $timetableEntry['relief-teacher-fullname']=<<< EOD
<a href="/RTSS/relief/_teacher_detail.php?accname={$teaching['relief-teacher-accname']}" class="teacher-detail-link">{$timetableEntry['relief-teacher-fullname']}</a>
EOD;

                        echo "<tr>";
                        if ($tInd == 0)
                        {
                            $rowspan=count($teachingList);
                            echo <<< EOD
    <td class="time-col" rowspan="$rowspan">$className</td>
EOD;
                        }
                        echo implode('', array_map(array("PageConstant", "tdWrap"), $timetableEntry)) . "</tr>";
                    }
                }
            }
            else
            {
             
            for ($i=0; $i < count($timeArr) - 1; $i++)
            {
                $teachingList=$timetable[$i];

                if ($teachingList)
                {
                    foreach ($teachingList as $tInd => $teaching)
                    {
                        PageConstant::escapeHTMLEntity($teaching);
                        $timetableEntry=array();
                        foreach (array_slice($headerKeyList, 1) as $key => $value)
                        {
                            $timetableEntry[$key]=$teaching[$key];
                        }

                        // Class name display/Account name
                        if ($orderByClass) 
                        {
                            $timetableEntry['time']= $timeArr[$timetableEntry['time-from']] . " - " . $timeArr[$timetableEntry['time-to']];
                        }
                        else
                        {
                            $timetableEntry['class']=implode(", ", $timetableEntry['class']);
                        }
                        
                        
                        $timetableEntry['teacher-fullname']=<<< EOD
<a href="/RTSS/relief/_teacher_detail.php?accname={$teaching['teacher-accname']}" class="teacher-detail-link">{$timetableEntry['teacher-fullname']}</a>
EOD;
                        $timetableEntry['relief-teacher-fullname']=<<< EOD
<a href="/RTSS/relief/_teacher_detail.php?accname={$teaching['relief-teacher-accname']}" class="teacher-detail-link">{$timetableEntry['relief-teacher-fullname']}</a>
EOD;

                        echo "<tr>";
                        if ($tInd == 0)
                        {
                            $rowspan=count($teachingList);
                            if ($orderByClass)
                            {
                                echo <<< EOD
    <td class="time-col" rowspan="$rowspan">{$timetableEntry['class']}</td>
EOD;
                            }
                            else
                            {
                                echo <<< EOD
    <td class="time-col" rowspan="$rowspan">{$timeArr[$i]}<span style="margin: 0 3px">-</span>{$timeArr[$i + 1]}</td>
EOD;
                            }
                        }
                        echo implode('', array_map(array("PageConstant", "tdWrap"), $timetableEntry)) . "</tr>";
                    }
                }
                else
                {
                    $otherTdStr=implode('', array_map(array("PageConstant", "tdWrap"), array_fill(0, count(NameMap::$TIMETABLE[$orderByClass ? 'layout2' : 'layout']['display'])-1, '')));
                    if ($orderByClass)
                    {
                        echo <<< EOD
    <tr><td class="time-col">{$timetableEntry['class']}</td>$otherTdStr</tr>
EOD;
                    }
                    else
                    {
                        echo <<< EOD
    <tr><td class="time-col">{$timeArr[$i]}<span style="margin: 0 3px">-</span>{$timeArr[$i + 1]}</td>$otherTdStr</tr>
EOD;
                    }
                }
            }    
                
            }
            ?>
        </tbody>
    </table>
</div>
<?php } ?>