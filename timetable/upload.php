<?php 
    include_once '../head-frag.php';
?>
<title><?php echo Constant::SCH_NAME_ABBR . " " . Constant::PRODUCT_NAME; ?></title>
<link href="/RTSS/css/main.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/timetable.css" rel="stylesheet" type="text/css">
<script src="/RTSS/js/upload.js"></script>

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
                    <li><a href="/RTSS/timetable/">Timetable</a></li>
                    <li>Upload</li>
                </ul>                
            </div>
            <form class="main" name="timetable" action="" method="post">
            	<div class="line"><span class="label">Year:</span>
                	<select name="year">
                        <?php 
                            $curYear=date('Y');
                            for ($i=$curYear-Constant::NUM_OF_YEAR; $i<=$curYear+Constant::NUM_OF_YEAR; $i++)
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
            </form>
        </div>        
    </div>
    <?php include '../sidebar-frag.php'; ?>
</div>
    
</body>
</html>