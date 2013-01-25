<?php 
    include_once '../head-frag.php'; include_once '../php-head.php';
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
            <div id="topbar">
            	<div class="fltrt">XXX | <a href="/RTSS/">Log out</a></div>
                <ul class="breadcrumb">
                    <li><a href="/RTSS/report/">Report</a></li>
                    <li>View</li>
                </ul>
            </div>
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