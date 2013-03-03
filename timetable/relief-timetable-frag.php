<div style="color: red; padding-bottom: 5px; margin-top: -10px">Relief classes are highlighted in red.
    <?php if ($NO_PREIVEW) { ?>
        <a href="print-individual.php" target="_blank" id="print-individual" class="button" style="float:right; margin-right: 30%; margin-top: -20px">Print</a>
    <?php } ?>
</div>
<table class="table-info" style="width: 70%">
    <thead>
        <tr>
            <?php
            $width=array('90px', '30%', '40%', '30%');

            $headerKeyList=NameMap::$TIMETABLE['individual']['display'];
            $tableHeaderList=array_values($headerKeyList);

            for ($i=0; $i < count($tableHeaderList); $i++)
            {
                echo <<< EOD
                    <th style="width: $width[$i]">$tableHeaderList[$i]</th>
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
                $otherTdContent=false;
                switch ($teaching['attr'])
                {
                    case -1:
                        $style='style="backgound-color: gray"';
                        break;
                    case 1:
                        $style='style="color: blue"';
                        break;
                    case 2:
                        $style='style="color: red"';
                        $otherTdContent=true;
                        break;
                }
                
                $timetableEntry=array();
                foreach (array_slice($headerKeyList, 1) as $key => $value)
                {
                    if ($otherTdContent)
                    {
                        $otherTdContent= <<< EOD
<span style="text-decoration: line-through;">{$teaching['skipped'][$key]}</span>   
EOD;
                    }
                    $timetableEntry[]= <<< EOD
<span $style>{$teaching[$key]}{$otherTdContent}</span>
EOD;
                }

                // Class name display
//                $timetableEntry[1]=implode(", ", $timetableEntry[1]);
//                
//                if ($teaching['skipped'])
//                {
//                    
//                }
                
                
//                if ($teaching['isRelief']) $style='style="color: red"';
                                                
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
<?php if ($_SESSION['type'] == 'admin') { ?>
<hr style="margin: 30px 10px 20px" />
<div style="padding-bottom: 15px; font-size: 1.2em">Relief Timetable:
    <?php if ($NO_PREIVEW) { ?>
        <a href="print-relief.php" target="_blank" id="print-relief" class="button" style="float:right; margin-right: 10%; margin-top: -5px">Print</a>
    <?php } ?>
</div>        
<table class="table-info">
    <thead>
        <tr>
            <?php
            $width=array('90px', '15%', '20%', '15%', '25%', '25%');

            $headerKeyList=NameMap::$TIMETABLE['layout']['display'];
            $tableHeaderList=array_values($headerKeyList);

            for ($i=0; $i < count($tableHeaderList); $i++)
            {
                echo <<< EOD
                    <th style="width: $width[$i]">$tableHeaderList[$i]</th>
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
<?php } ?>