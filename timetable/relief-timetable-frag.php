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
                    echo implode('', array_map("tdWrap", $timetableEntry)) . "</tr>";
                }
            }
            else
            {
                $otherTdStr=implode('', array_map("tdWrap", array_fill(0, count(NameMap::$TIMETABLE['layout']['display']), '')));
                echo <<< EOD
<tr><td class="time-col">{$timeArr[$i]}<span style="margin: 0 3px">-</span>{$timeArr[$i + 1]}</td>$otherTdStr</tr>
EOD;
            }
        }
        ?>
    </tbody>
</table>