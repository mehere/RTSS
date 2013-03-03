<?php
include_once '../php-head.php';
include_once '../head-frag.php';
?>
<title><?php echo PageConstant::SCH_NAME_ABBR . " " . PageConstant::PRODUCT_NAME; ?></title>
<link href="/RTSS/css/main.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/relief.css" rel="stylesheet" type="text/css" />
<!--<script src="/RTSS/js/relief.js"></script>-->

<link href="/RTSS/jquery-ui/ui-lightness/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css" />
<script src="/RTSS/jquery-ui/jquery-ui-1.9.2.custom.min.js"></script>

<style type="text/css">
.bt-control .button {
	width: 100px;
}

.section .table-info tbody tr {
	background-color: #fff;
}
.table-info tbody tr:hover {
	background: none;
}

</style>
</head>
<body>

<div id="container">
    <div id="content-wrapper">
    	<div id="content">
            <?php
                $TOPBAR_LIST=array(
                    array('tabname'=>'Scheduling', 'url'=>"/RTSS/relief/"),
                    array('tabname'=>'Ad Hoc Schedule', 'url'=>""),
                );
                include '../topbar-frag.php';
            ?>
            <form class="main" name="schedule" action="" method="post">            	
                <div class="section">
                	Previous Schedule Result:
                    <table class="table-info">
                        <thead>
                            <tr class="teacher-thead">
                                <?php
                                    $width=array('50%', '110px', '50%', '50px', '170px');
                                    $tableHeaderList=array_values(NameMap::$RELIEF_EDIT['adhocSchedule']['display']);

                                    for ($i=0; $i<count($tableHeaderList); $i++)
                                    {
                                        // class="sort"
                                        echo <<< EOD
                                            <th style="width: $width[$i]">$tableHeaderList[$i]<!--span class="ui-icon ui-icon-arrowthick-2-n-s"></span--></th>
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
    <select name="busy-from-$i"><option value="">--</option>$timeFromOptionStr</select> - 
    <select name="busy-to-$i"><option value="">--</option>$timeToOptionStr</select>
</td>
EOD;
                                            $isFirstRow=false;
                                        }
                                        
                                        echo <<< EOD
<tr>$firstRowSpanName<td>$timeFrom - $timeTo</td><td>$classStr</td>
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
            </form>
            <div id="dialog-alert"></div>
        </div>
    </div>
    <?php include '../sidebar-frag.php'; ?>
</div>

</body>
</html>
