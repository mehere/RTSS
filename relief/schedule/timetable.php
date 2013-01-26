<?php 
    include_once '../../php-head.php';
    include_once '../../head-frag.php';
?>
<title><?php echo PageConstant::SCH_NAME_ABBR . " " . PageConstant::PRODUCT_NAME; ?></title>
<link href="/RTSS/css/main.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/relief.css" rel="stylesheet" type="text/css" />
<!--<link href="/RTSS/css/timetable.css" rel="stylesheet" type="text/css" />
<script src="/RTSS/js/timetable.js"></script>-->

<link href="/RTSS/jquery-ui/ui-lightness/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css" />
<script src="/RTSS/jquery-ui/jquery-ui-1.9.2.custom.min.js"></script>

</head>
<body>

<div id="container">  	
    <div id="content-wrapper">
    	<div id="content">
            <div id="topbar">
            	<div class="fltrt">XXX | <a href="/RTSS/">Log out</a></div>
                <ul class="breadcrumb">
                	<li><a href="/RTSS/relief/">Scheduling</a></li>
                    <li><a href="/RTSS/relief/schedule/">Result Preview</a></li>
                    <li><a href="/RTSS/relief/schedule/result.php">Result Approval</a></li>
                    <li>Timetable Preview</li>
                </ul>                
            </div>
            <div id="tabs">
            	<div class="gradient-top"></div>
                <ul>
                    <li><a href="/RTSS/timetable/_timetable.php?mode=preview">Class</a></li>
                    <li><a href="/RTSS/timetable/_timetable.php?tab=teacher&mode=preview">Teacher</a></li>
                </ul>                
            </div>
            <form name="tab-data"><input type="hidden" name="selectedInd" value="<?php if ($_GET['tab']=='teacher') echo 1; ?>" /></form>
        </div>        
    </div>
    <?php include '../../sidebar-frag.php'; ?>
</div>
    
</body>
</html>