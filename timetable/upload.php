<?php 
    include_once '../head-frag.php'; include_once '../php-head.php';
?>
<title><?php echo PageConstant::SCH_NAME_ABBR . " " . PageConstant::PRODUCT_NAME; ?></title>
<link href="/RTSS/css/main.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/upload.css" rel="stylesheet" type="text/css">
<script src="/RTSS/js/upload.js"></script>

<link href="/RTSS/jquery-ui/ui-lightness/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css" />
<script src="/RTSS/jquery-ui/jquery-ui-1.9.2.custom.min.js"></script>

</head>
<body>

<div id="container">  	
    <div id="content-wrapper">
    	<div id="content">
            <?php 
                $TOPBAR_LIST=array(
                    array('tabname'=>'Timetable', 'url'=>"/RTSS/timetable/upload.php"), 
                    array('tabname'=>'Admin', 'url'=>""), 
                );
                include '../topbar-frag.php';
            ?>
            <form class="main" name="timetable" action="" method="post">
            	<div class="line"><span class="label">Year:</span>
                	<select name="year">
                        <?php 
                            $curYear=date('Y');
                            for ($i=$curYear-PageConstant::NUM_OF_YEAR; $i<=$curYear+PageConstant::NUM_OF_YEAR; $i++)
                            {
                                $selected=$i==$curYear ? 'selected="selected"' : '';
                                echo <<< EOD
                                    <option value="$i" $selected >$i</option>
EOD;
                            }
                        ?>                    	
                    </select>
                </div>
                <div class="line"><span class="label">Semester:</span>
                	<select name="sem">
                    	<option value="1">1</option>
                    	<option value="2">2</option>                        
                    </select>
                </div>
                <div class="line"><span class="label">File:</span><input type="file" name="timetableFile" /></div>
                <div class="line"><span class="label">&nbsp;</span><input type="submit" value="Upload" name="submit" style="font-size: .9em; margin: 10px 0" class="button" /></div>                
            </form>
            <form name="AED" action="" method="post" class="main">
            	AED Timetable:
                <table class="table-info">
                	<thead>
                        <th style="width: 110px"></th>
                        <?php 
                            $dayArr=PageConstant::$DAY;
                            foreach($dayArr as $day)
                            {
                                echo <<< EOD
                                    <th style="width: 20%">$day</th>
EOD;
                            }
                        ?>                    	
                    </thead>
                    <tbody>
                        <?php 
                            $timeArr=array();
                            for ($i=0; $i<(PageConstant::$SCHOOL_END_TIME-PageConstant::$SCHOOL_START_TIME)/PageConstant::SCHOOL_TIME_INTERVAL/60; $i++)
                            {
                                $timeStr=date("H:i", $i*PageConstant::SCHOOL_TIME_INTERVAL*60+PageConstant::$SCHOOL_START_TIME);
                                $timeToStr=date("H:i", ($i+1)*PageConstant::SCHOOL_TIME_INTERVAL*60+PageConstant::$SCHOOL_START_TIME);
                                $timeArr[$i]=$timeStr;
                                echo <<< EOD
<tr><td class="time-col">$timeStr - $timeToStr</td><td></td><td></td><td></td><td></td><td></td></tr>
EOD;
                            }
                        ?>
                    </tbody>
                </table>
            </form>
            <form name="add-class" action="">
        </div>        
    </div>
    <?php include '../sidebar-frag.php'; ?>
</div>
    
</body>
</html>