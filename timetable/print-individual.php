<?php 
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

Template::validate(false, false, true);

if ($_SESSION['type'] != 'admin' && $_SESSION['type'] != 'super_admin')
{
    $_GET['accname']=$_SESSION['accname'];
}

$date=$_GET['date'];
if (!$date)
{
    die('Please following the link on the previous page to redirect to the print view.');
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
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
            Timetable on <em><?php echo SchoolTime::convertDate($date, 2); ?></em>
            <div style="font-size: 16px"><?php echo "Sem " . SchoolTime::getSemYearFromDate(0, new DateTime($_GET['date'])) . ", " . SchoolTime::getSemYearFromDate(1, new DateTime($_GET['date'])); ?></div>
        </h2>
        <div style="padding-bottom: 5px; margin-top: -10px">
            <strong style="font-size: 1.1em;">
                <?php 
                    $teacherList=ListGenerator::getTeacherName($_GET['date']); 
                    echo $teacherList[$_GET['accname']]; 
                ?>
            </strong>
            (<span>Relief classes are in <strong>bold</strong>.</span>
            <span>For AED: classes <u>underlined</u> are not mandatory.</span>)
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
                PageConstant::escapeHTMLEntity($timetableIndividual);

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
                                $style='style="text-decoration: underline"';
                                break;
                            case 2:
                                $style='style="font-weight: bold"';
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
</body>
</html>