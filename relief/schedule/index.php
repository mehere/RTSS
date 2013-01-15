<?php 
    include_once '../../php-head.php';
    include_once '../../head-frag.php';
?>
<title><?php echo Constant::SCH_NAME_ABBR . " " . Constant::PRODUCT_NAME; ?></title>
<link href="/RTSS/css/main.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/schedule.css" rel="stylesheet" type="text/css" />
</head>
<body>

<div id="container">  	
    <div id="content-wrapper">
    	<div id="content">
            <div id="topbar">
            	<div class="fltrt">XXX | <a href="/RTSS/">Log out</a></div>
                <ul class="breadcrumb">
                    <li><a href="/RTSS/relief/">Scheduling</a></li>
                    <li>Result Preview</li>
                </ul>                
            </div>
            <form class="result" method="get" action="result.php">
                <fieldset>
                    <legend>Schedule 1</legend>
                    <table class="result-table">
                    	<tr><td>Type:</td><td>Relief</td></tr>
                        <tr><td>Min Period:</td><td>2</td></tr>
                        <tr><td>Subject:</td><td>Almost</td></tr>
                    </table>
                    <input type="submit" value="View" class="button" />
                </fieldset>
                <input type="hidden" name="scheduleIndex" value="0" />                
            </form>
            <form class="result">
                <fieldset>
                    <legend>Schedule 2</legend>
                    <table class="result-table">
                    	<tr><td>Type:</td><td>AED</td></tr>
                        <tr><td>Min Period:</td><td>3</td></tr>
                        <tr><td>Subject:</td><td>Almost</td></tr>
                    </table>
                </fieldset>
            </form>
        </div>        
    </div>
    <?php include '../../sidebar-frag.php'; ?>
</div>
    
</body>
</html>