<?php 
$BYPASS_ADMIN=true;
include_once '../php-head.php';

require_once '../class/TimetableDB.php';
require_once '../class/ListGenerator.php';

if ($_SESSION['type'] != 'admin')
{
    $_GET['accname']=$_SESSION['accname'];
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
            Timetable on <em><?php echo date('D ' . PageConstant::DATE_FORMAT_SG); ?></em>
            <div style="font-size: 16px"><?php echo "Sem " . PageConstant::printSemRange(true) . ", " . PageConstant::printYearRange(true); ?></div>
        </h2>
        <div style="padding-bottom: 5px; margin-top: -10px">
            <strong style="font-size: 1.1em;">
                <?php 
                    $teacherList=ListGenerator::getTeacherName($_GET['date']); 
                    echo $teacherList[$_GET['accname']]; 
                ?>
            </strong>
            <span style="color: red">(Relief classes are highlighted in red.)</span>
        </div>        
        <table class="table-info">
            <thead>
                <tr>
                    <?php
                    $width=array('110px', '30%', '40%', '30%');

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
                $timetableIndividual=TimetableDB::getIndividualTimetable($_GET['date'], $_GET['accname']);

                $timeArr=SchoolTime::getTimeArrSub(0, 0);
                for ($i=0; $i < count($timeArr) - 1; $i++)
                {
                    $teaching=$timetableIndividual[$i];

                    if ($teaching)
                    {
                        PageConstant::escapeHTMLEntity($teaching);
                        $timetableEntry=array();
                        foreach (array_slice($headerKeyList, 1) as $key => $value)
                        {
                            $timetableEntry[]=$teaching[$key];
                        }

                        // Class name display
                        $timetableEntry[1]=implode(", ", $timetableEntry[1]);

                        $style="";
                        if ($teaching['isRelief']) $style='style="color: red"';

                        $otherTdStr=implode('', array_map(array("PageConstant", "tdWrap"), $timetableEntry));
                        echo <<< EOD
<tr $style><th class="time-col">{$timeArr[$i]}<span style="margin: 0 3px">-</span>{$timeArr[$i + 1]}</th>$otherTdStr</tr>
EOD;
                    }
                    else
                    {
                        $otherTdStr=implode('', array_map(array("PageConstant", "tdWrap"), array_fill(0, count(NameMap::$TIMETABLE['individual']['display']), '')));
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