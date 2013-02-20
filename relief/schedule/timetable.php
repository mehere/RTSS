<?php 
include_once '../../php-head.php';   

include_once '../../head-frag.php';
?>
<title><?php echo PageConstant::SCH_NAME_ABBR . " " . PageConstant::PRODUCT_NAME; ?></title>
<link href="/RTSS/css/main.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/timetable.css" rel="stylesheet" type="text/css" />

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
                    array('tabname' => 'Result Approval', 'url' => "/RTSS/relief/schedule/result.php"),
                    array('tabname' => 'Result Preview', 'url' => "")                    
                );
                include '../../topbar-frag.php';
            ?>
            <div class="main">                
                <?php 
                    require_once '../../class/TimetableDB.php';
                    
                    $timetable=array(); // <-- to be changed
                    
                    $timetableIndividual=array(0=>array('class'=>array('1F', '2A'), 'subject'=>'Physics', 'venue'=>'LT30'),
                        3=>array('class'=>array('1F2A'), 'subject'=>'Chemistry', 'venue'=>'LT10', 'isRelief'=>true));
                    
                    include '../../timetable/relief-timetable-frag.php'; 
                ?>
            </div>
            <div class="bt-control">
                <a href="result.php" class="button">Go Back</a>
            </div>
        </div>        
    </div>
    <?php include '../../sidebar-frag.php'; ?>
</div>
    
</body>
</html>