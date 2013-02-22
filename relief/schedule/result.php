<?php 
include_once '../../php-head.php';

require_once '../../class/SchedulerDB.php';

include_once '../../head-frag.php';        
?>
<title><?php echo PageConstant::SCH_NAME_ABBR . " " . PageConstant::PRODUCT_NAME; ?></title>
<link href="/RTSS/css/main.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/relief.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/schedule-result.css" rel="stylesheet" type="text/css">
<script src="/RTSS/js/result.js"></script>

<link href="/RTSS/jquery-ui/ui-lightness/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css" />
<script src="/RTSS/jquery-ui/jquery-ui-1.9.2.custom.min.js"></script>

</head>
<body>

<div id="container">  	
    <div id="content-wrapper">
    	<div id="content">
            <?php
                $TOPBAR_LIST=array(
                    array('tabname' => 'Scheduling', 'url' => "/RTSS/relief/"),
                    array('tabname' => 'Result Approval', 'url' => "")
                );
                include '../../topbar-frag.php';
            ?>
            <form class="main" name="edit" action="" method="post">
                <div class="section">
                    <table class="table-info">
                        <thead>
                            <tr>
                                <?php                                 
                                    $width=array('24%', '130px', '38%', '38%');                                                                        
                                                                        
                                    $tableHeaderList=array_values(NameMap::$SCHEDULE_RESULT['schedule']['display']);
                                    
                                    for ($i=0; $i<count($tableHeaderList); $i++)
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
                                $scheduleList=array(0=>array(
                                    array('class'=>array('1F', '2A'), 'time'=>array(1, 3),
                                    "teacherOnLeave"=>'Ann', 'reliefTeacher'=>'Bob', 
                                    "teacherAccName" =>'S12345', "reliefAccName" => 'T!@#$%'),
                                    array('class'=>array('4F', '9A'), 'time'=>array(1, 3),
                                    "teacherOnLeave"=>'Tom', 'reliefTeacher'=>'Jerry', 
                                    "teacherAccName" =>'S0012345', "reliefAccName" => 'TXX!@#$%'),
                                    array('class'=>array('4A', '9C'), 'time'=>array(6, 8),
                                    "teacherOnLeave"=>'Tom', 'reliefTeacher'=>'Jerry', 
                                    "teacherAccName" =>'S0012345', "reliefAccName" => 'TXX!@#$%')));
                                
                                foreach ($scheduleList[0] as $key => $value)
                                {
                                    $classStr=implode(', ', $value['class']);
                                    $timeStart=SchoolTime::getTimeValue($value['time'][0]);
                                    $timeEnd=SchoolTime::getTimeValue($value['time'][1]);
                                    echo <<< EOD
<tr><td>$classStr</td>
    <td>
        $timeStart<span style="margin: 0 3px">-</span>$timeEnd</td><td>{$value['teacherOnLeave']}
        <input type="hidden" name="time-start-$key" value="{$value['time'][0]}" />
        <input type="hidden" name="time-end-$key" value="{$value['time'][1]}" />
    </td>
    <td>
        <span class="text-display">{$value['reliefTeacher']}</span>
        <input type="text" name="relief-teacher-$key" value="{$value['reliefTeacher']}" class="text-hidden" />
        <input type="hidden" name="relief-accname-$key" value="{$value['reliefAccName']}" />
    </td>
</tr>
EOD;
                                }
                                
                                $scheduleResultNum=$_SESSION['scheduleResultNum'];
                                if (!$scheduleResultNum) 
                                {
                                    $scheduleResultNum=$_SESSION['scheduleResultNum']=SchedulerDB::scheduleResultNum();
                                }
                                if ($scheduleResultNum == 0)
                                {
                                    $scheduleResultNum=1;
                                    
                                    $otherTdStr=implode('', array_map(array("PageConstant", "tdWrap"), array_fill(0, count($tableHeaderList), '--')));                                            
                                    echo "<tr>$otherTdStr</tr>";
                                }
                            ?>
                        </tbody>
                    </table>
                    <div class="page-control">                    	
                        <?php
                            $curPage=$_GET['page'];
                            if (!$curPage) $curPage=1;
                            
                            $prevPage=max(1, $curPage-1);
                            echo <<< EOD
<a href="?page=$prevPage" class="page-no page-turn">&lt;</a>   
EOD;
                            
                            for ($i=1; $i<=$scheduleResultNum; $i++)
                            {
                                $selectedStr='';
                                if ($curPage == $i) $selectedStr='page-selected';
                                echo <<< EOD
<a href="?page=$i" class="page-no $selectedStr">$i</a>
EOD;
                            }
                            
                            $nextPage=min($scheduleResultNum, $curPage+1);
                            echo <<< EOD
<a href="?page=$nextPage" class="page-no page-turn">&gt;</a>   
EOD;
                        ?>
                    </div>
                </div>
                <div class="fltrt">                	
                	<input type="button" name="override" value="Override" class="button" />
                    <input type="submit" value="Approve" class="button" />
                </div>
                <div class="link-control">
                    <a href="timetable.php?schedule=<?php echo $curPage; ?>" class="link">Preview Timetable</a>
                </div>
                <input type="hidden" name="num" value="<?php echo count($scheduleList); ?>" />
                <input type="hidden" name="schedule-index" value="<?php echo $curPage; ?>" />
            </form>
            <div id="dialog-alert"></div>
        </div>
    </div>
    <?php 
        include '../../sidebar-frag.php'; 
    
        unset($_SESSION['timetableAnalyzer']);
        unset($_SESSION['abbrNameList']);        
    ?>
</div>
    
</body>
</html>