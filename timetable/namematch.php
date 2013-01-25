<?php 
    include_once '../php-head.php';
    include_once '../head-frag.php';        
?>
<title><?php echo PageConstant::SCH_NAME_ABBR . " " . PageConstant::PRODUCT_NAME; ?></title>
<link href="/RTSS/css/main.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/relief.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/namematch.css" rel="stylesheet" type="text/css">
<!--script src="/RTSS/js/result.js"></script-->

<link href="/RTSS/jquery-ui/ui-lightness/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css" />
<script src="/RTSS/jquery-ui/jquery-ui-1.9.2.custom.min.js"></script>

</head>
<body>

<div id="container">  	
    <div id="content-wrapper">
    	<div id="content">
            <?php 
                $TOPBAR_LIST=array(
                    array('tabname'=>'Timetable', 'url'=>"/RTSS/timetable/admin.php"), 
                    array('tabname'=>'Name Match', 'url'=>""), 
                );
                include '../topbar-frag.php';
            ?>
            <form class="main" name="edit" action="" method="post">
                <div class="section">
                    <table class="table-info">
                        <thead>
                            <tr>
                                <?php                                 
                                    $width=array('150px', '100%');
                                                                        
                                    $tableHeaderList=array_values(NameMap::$TIMETABLE['namematch']['display']);
                                    
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
                        	<tr>
                            	<td>ASCNAME</td>
                            	<td>
                                	<select name="fullname">
                                    	<optgroup label="Suggested">
                                        	<option value="accxxx">Tan Hong Lin</option>
                                        </optgroup>
                                        <optgroup label="All Teachers">
                                        	<option value="asd">Astin Sun</option>
                                        </optgroup>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="link-control">
                    <input type="submit" value="Submit" class="button fltrt" />
                </div>               
            </form>            
        </div>        
    </div>
    <?php include '../sidebar-frag.php'; ?>
</div>
    
</body>
</html>