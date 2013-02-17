<?php 
include_once '../php-head.php';

function tdWrap($ele)
{
    return "<td>$ele</td>";
}

$isAdmin=false;
if ($_SESSION['type'] == 'admin')
{
    $isAdmin=true;
}

include_once '../head-frag.php';
?>
<title><?php echo PageConstant::SCH_NAME_ABBR . " " . PageConstant::PRODUCT_NAME; ?></title>

<link href="/RTSS/jquery-ui/ui-lightness/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css" />
<script src="/RTSS/jquery-ui/jquery-ui-1.9.2.custom.min.js"></script> 

<link href="/RTSS/css/main.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/timetable.css" rel="stylesheet" type="text/css" />
<script src="/RTSS/js/timetable.js"></script>

</head>
<body>

<div id="container">
    <div id="content-wrapper">
        <div id="content">
            <?php
            $TOPBAR_LIST=array(
                array('tabname' => 'Timetable', 'url' => "/RTSS/timetable/"),
                array('tabname' => 'View', 'url' => ""),
            );
            include '../topbar-frag.php';
            ?>
            <div class="main">
                <form name="switch" class="control" action="" method="post">
                    <?php
                        require_once '../class/ListGenerator.php';
                        require_once '../class/TimetableDB.php';
                        
                        $class=$_POST['class'];
                        $teacher=$isAdmin?$_POST['teacher']:$_SESSION['accname'];
                    
                        $date=$_POST['date'];
                        if (!$date)
                        {
                            $date=$_SESSION['scheduleDate'];
                        }
                    ?>
                    <div class="line">Date: <input type="text" class="textfield" name="date-display" maxlength="10" /><input type="hidden" name="date" value="<?php echo $date; ?>" /> <img id="calendar-trigger" src="/RTSS/img/calendar.gif" alt="Calendar" style="vertical-align: middle; cursor: pointer" />
                        <?php if ($isAdmin) { ?>
                        <select name="class" style="margin-left: 30px">
                            <option value="">-- Any --</option>
                            <?php echo PageConstant::formatOptionInSelect(ListGenerator::getClassName($date), $class, true); ?>
                        </select>
                        <select name="teacher">
                            <option value="">-- Any --</option>
                            <?php echo PageConstant::formatOptionInSelect(ListGenerator::getTeacherName($date), $teacher); ?>
                        </select>
                        <?php } ?>
                    </div>
                </form>
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
                        $timetable=TimetableDB::getReliefTimetable($teacher, $class, $date);

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
            </div>
        </div>        
    </div>
    <?php include '../sidebar-frag.php'; ?>
</div>
    
</body>
</html>