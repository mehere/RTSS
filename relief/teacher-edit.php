<?php 
    include_once '../head-frag.php'; include_once '../php-head.php';
    
    $isTemp=$_GET['teacher'] == 'temp';
?>
<title><?php echo PageConstant::SCH_NAME_ABBR . " " . PageConstant::PRODUCT_NAME; ?></title>
<link href="/RTSS/css/main.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/relief.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/relief-edit.css" rel="stylesheet" type="text/css">
<script src="/RTSS/js/relief-edit.js"></script>
<script src="/RTSS/js/teacher-detail.js"></script>

<link href="/RTSS/jquery-ui/ui-lightness/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css" />
<script src="/RTSS/jquery-ui/jquery-ui-1.9.2.custom.min.js"></script>

</head>
<body>

<div id="container">  	
    <div id="content-wrapper">
    	<div id="content">
            <?php 
                $TOPBAR_LIST=array(
                    array('tabname'=>'Scheduling', 'url'=>"/RTSS/relief/"), 
                    array('tabname'=>'Edit/Add', 'url'=>""), 
                );
                include '../topbar-frag.php';
//                var_dump($_SESSION['scheduleDate']);
            ?>
            <form class="main" name="edit" action="" method="post">            	
            	<input type="hidden" name="prop" value="<?php echo $isTemp?'temp':'leave'; ?>" />
                <div class="section">
                	<?php echo $isTemp ? "Temporary Relief Teacher:" : "Teacher on Leave:"; ?> 
                    <div class="control-top"><a href="">Select All</a> <a href="">Deselect All</a></div>
                    <table class="table-info">
                        <thead>
                            <tr>
                                <?php                                 
                                    $width=array('60px', '30%', '20%', '240px', '50%', '70px');
                                                                        
                                    $tableHeaderList=array_values(NameMap::$RELIEF_EDIT[$isTemp ? 'tempTeacher' : 'teacherOnLeave']['display']);
                                    array_unshift($tableHeaderList, 'Select');
                                    $tableHeaderList[]='Verified';
                                    
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
                        	<tr><td><input type="checkbox" name="select-0" /></td><td><a href="_teacher_detail.php?accname=1234">haha asdf</a></td>
                            	<td>
                                	<select name="reason"><option value="MC">MC</option>
                                    </select>
                                </td>
                            	<td>
                                	<div class="time-line">From: <input type="text" name="date-from-0" maxlength="10" style="width: 7em; margin-right: 5px" /><input type="hidden" name="server-date-from-0" value="2012-12-10" />
                                		<select name="time-from-0"><option value="15">15</option></select>
                                    </div>
                                    <div class="time-line">To: <input type="text" name="date-to-0" maxlength="10" style="width: 7em; margin-right: 5px" /><input type="hidden" name="server-date-to-0" value="2012-12-10" />
                                		<select name="time-to-0"><option value="15">30</option></select>
                                    </div>
                                </td>
                                <td><textarea name="remark-0">Specifies that a text area should automatically</textarea></td><td><input type="checkbox" name="verified-0" /></td>
                            </tr>
                            <tr id="last-row"><td><button name="line-delete" style="padding: 5px 0"></button></td>
                            <td><input type="text" name="fullname" style="width: 90%;" /></td>
                            	<td>
                                	<select name="reason"><option value="MC">MC</option>
                                    </select>
                                </td>
                            	<td>
                                	<div class="time-line">From: <input type="text" name="date-from" maxlength="10" style="width: 7em; margin-right: 5px" />
                                		<select name="time-from"><option value="15">15</option></select>
                                    </div>
                                    <div class="time-line">To: <input type="text" name="date-to" maxlength="10" style="width: 7em; margin-right: 5px" />
                                		<select name="time-to"><option value="15">30</option></select>
                                    </div>
                                </td>
                                <td><textarea name="remark"></textarea></td><td></td>
                            </tr>
                        </tbody>
                    </table>
                    <a href="" id="add-save">Add</a>
                </div>
                <div class="bt-control">
                	<input type="button" name="verify" value="Verify Selected" class="button" />
                    <input type="button" name="delete" value="Delete Selected" class="button" />
                </div>
                <input type="hidden" name="num" value="1" />             
            </form>
            <div id="dialog-confirm"></div>
            <div id="teacher-detail">Loading ...</div>
        </div>        
    </div>
    <?php include '../sidebar-frag.php'; ?>
</div>
    
</body>
</html>