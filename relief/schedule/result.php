<?php 
    include_once '../../php-head.php';
    include_once '../../head-frag.php';        
?>
<title><?php echo Constant::SCH_NAME_ABBR . " " . Constant::PRODUCT_NAME; ?></title>
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
            <div id="topbar">
            	<div class="fltrt">XXX | <a href="/RTSS/">Log out</a></div>
                <ul class="breadcrumb">
                    <li><a href="/RTSS/relief/">Scheduling</a></li>
                    <li><a href="/RTSS/relief/schedule/">Result Preview</a></li>
                    <li>Result Approval</li>
                </ul>                
            </div>
            <form class="main" name="edit" action="" method="post">
                <div class="section">
                    <table class="table-info">
                        <thead>
                            <tr>
                                <?php                                 
                                    $width=array('60px', '130px', '40%', '60%');
                                                                        
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
                        	<tr><td>1A</td><td>1030 - 1330</td><td>Good Anota</td><td><span class="text-display">What Hash</span><input type="text" name="reliefTeacherName-0" value="What Hash" class="text-hidden" /></td></tr>
                            <tr><td>1A</td><td>1030 - 1330</td><td>Good Anota</td><td>What Hash</td></tr>
                            <tr><td>1A</td><td>1030 - 1330</td><td>Good Anota</td><td>What Hash</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="fltrt">                	
                	<input type="button" name="override" value="Override" class="button" />
                    <input type="submit" value="Approve" class="button" />
                </div>
                <div class="link-control">
                    <a href="timetable.php?tab=teacher" class="link">Preview Teacher Timetable</a>
                    <a href="timetable.php" class="link">Preview Class Timetable</a>
                </div>                
                <input type="hidden" name="num" value="1" />                
            </form>            
        </div>        
    </div>
    <?php include '../../sidebar-frag.php'; ?>
</div>
    
</body>
</html>