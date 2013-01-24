<?php 
    include_once '../php-head.php';
    include_once '../head-frag.php';
?>
<title><?php echo PageConstant::SCH_NAME_ABBR . " " . PageConstant::PRODUCT_NAME; ?></title>
<link href="/RTSS/css/main.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/tab.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/relief.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/report.css" rel="stylesheet" type="text/css">
<script src="/RTSS/js/teacher-detail.js"></script>
<script src="/RTSS/js/report.js"></script>

<link href="/RTSS/jquery-ui/ui-lightness/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css" />
<script src="/RTSS/jquery-ui/jquery-ui-1.9.2.custom.min.js"></script>

</head>
<body>

<div id="container">  	
    <div id="content-wrapper">
    	<div id="content">
            <?php 
                $TOPBAR_LIST=array(
                    array('tabname'=>'Report', 'url'=>"/RTSS/report/"), 
                    array('tabname'=>'View', 'url'=>""), 
                );
                include '../topbar-frag.php';
            ?>
            <div id="tabs">
            	<div class="gradient-top"></div>
                <ul>
                    <li><a href="overall-frag.php">Overall</a></li>
                    <li><a href="individual-frag.php">Individual</a></li>
                </ul>
            </div>
            <div id="teacher-detail">Loading ...</div>
        </div>        
    </div>
    <?php include '../sidebar-frag.php'; ?>
</div>
    
</body>
</html>