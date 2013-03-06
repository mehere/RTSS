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
        <div class="control-top"><a href="print-individual.php" id="print-individual" target="_blank">Print</a></div>
    <?php } ?>
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

            $timeArr=SchoolTime::getTimeArrSub(0, 0);
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
                    $otherTdStr=implode('', array_map(array("PageConstant", "tdWrap"), array_fill(0, count(NameMap::$TIMETABLE['individual']['display']), '')));
                    echo <<< EOD
    <tr><td class="time-col">{$timeArr[$i]}<span style="margin: 0 3px">-</span>{$timeArr[$i + 1]}</td>$otherTdStr</tr>
EOD;
                }
            }
            ?>
        </tbody>
    </table>
</div>

<?php if ($_SESSION['type'] == 'admin') { ?>
<div class='accordion colorbox green'>
    <a href="" class="icon-link"><img src="/RTSS/img/minus-white.png" /><img src="/RTSS/img/plus-white.png" style="display: none" /></a>
    <span class="box-title">
        Relief Timetable
    </span>
    <?php if ($NO_PREIVEW) { ?>
        <div class="control-top"><a href="print-relief.php" id="print-relief" target="_blank">Print</a></div>
    <?php } ?>
</div>
<div>
    <table class="hovered table-info">
        <thead>
            <tr>
                <?php
                $width=array('120px', '15%', '20%', '15%', '25%', '25%');

                $headerKeyList=NameMap::$TIMETABLE['layout']['display'];
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
            // Define $timetable in the calling function
            // $timetable=TimetableDB::getReliefTimetable($teacher, $class, $date);

            $timeArr=SchoolTime::getTimeArrSub(0, 0);
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
                            $timetableEntry[]=$teaching[$key];
                        }

                        // Class name display
                        $timetableEntry[1]=implode(", ", $timetableEntry[1]);

                        echo "<tr>";
                        if ($tInd == 0)
                        {
                            $rowspan=count($teachingList);
                            echo <<< EOD
    <td class="time-col" rowspan="$rowspan">{$timeArr[$i]}<span style="margin: 0 3px">-</span>{$timeArr[$i + 1]}</td>
EOD;
                        }
                        echo implode('', array_map(array("PageConstant", "tdWrap"), $timetableEntry)) . "</tr>";
                    }
                }
                else
                {
                    $otherTdStr=implode('', array_map(array("PageConstant", "tdWrap"), array_fill(0, count(NameMap::$TIMETABLE['layout']['display']), '')));
                    echo <<< EOD
    <tr><td class="time-col">{$timeArr[$i]}<span style="margin: 0 3px">-</span>{$timeArr[$i + 1]}</td>$otherTdStr</tr>
EOD;
                }
            }
            ?>
        </tbody>
    </table>
</div>
<?php } ?>