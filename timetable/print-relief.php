<?php 
include_once '../php-head.php';

require_once '../class/TimetableDB.php';

$date=$_GET['date'];
if (!$date)
{
    die('Please following the link on the previous page to redirect to the print view.');
}

include_once '../head-frag.php';
?>
<title>Timetable - Print</title>
<link href="/RTSS/css/print.css" rel="stylesheet" type="text/css">
<style type="text/css">
.table-info {
	width: 80%;
}
.table-info tbody tr th {
	background-color: #C9C9C9;
	color: black;
}
</style>
</head>

<body>
	<div id="container">
        <h2>
            Timetable on <em><?php echo SchoolTime::convertDate($date); ?></em>
            <div style="font-size: 16px"><?php echo "Sem " . PageConstant::printSemRange(true) . ", " . PageConstant::printYearRange(true); ?></div>
        </h2>        
        <div style="color: red; padding-bottom: 5px; margin-top: -10px">Relief classes are highlighted in red.</div>
        <table class="table-info">
            <thead>
                <tr>
                    <?php
                    $width=array('110px', '15%', '20%', '15%', '25%', '25%');

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
                $timetable=TimetableDB::getReliefTimetable('', '', $_GET['date']);
                PageConstant::escapeHTMLEntity($timetable);

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
<th class="time-col" rowspan="$rowspan">{$timeArr[$i]}<span style="margin: 0 3px">-</span>{$timeArr[$i + 1]}</th>
EOD;
                            }
                            echo implode('', array_map(array("PageConstant", "tdWrap"), $timetableEntry)) . "</tr>";
                        }
                    }
                    else
                    {
                        $otherTdStr=implode('', array_map(array("PageConstant", "tdWrap"), array_fill(0, count(NameMap::$TIMETABLE['layout']['display']), '')));
                        echo <<< EOD
<tr><th class="time-col">{$timeArr[$i]}<span style="margin: 0 3px">-</span>{$timeArr[$i + 1]}</th>$otherTdStr</tr>
EOD;
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>