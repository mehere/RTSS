<?php 
include_once '../php-head.php';

$isTemp=$_GET['teacher'] == 'temp';

include_once '../head-frag.php'; 
?>
<title><?php echo PageConstant::SCH_NAME_ABBR . " " . PageConstant::PRODUCT_NAME; ?></title>
<link href="/RTSS/css/main.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/relief.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/relief-edit.css" rel="stylesheet" type="text/css">
<script src="/RTSS/js/relief-edit.js"></script>

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
                
                require_once '../class/Teacher.php';                
            ?>
            <form class="main" name="edit" action="_teacher_edit.php" method="post">            	
            	<input type="hidden" name="prop" value="<?php echo $isTemp?'temp':'leave'; ?>" />
                <div class="section">
                	<?php echo $isTemp ? "Temporary Relief Teacher:" : "Teacher on Leave:"; ?> 
                    <div class="control-top"><a id="select-all" href="">Select All</a> <a id="deselect-all" href="">Deselect All</a></div>
                    <table class="table-info">
                        <thead>
                            <tr>
                                <?php                                 
                                    $width=$isTemp ? array('45px', '40px', '40%', '170px', '100px',  '245px', '60%') : 
                                        array('45px', '40px', '40%', '170px', '245px', '60%', '70px');
                                        
                                    $teacherKey=$isTemp? 'tempTeacher' : 'teacherOnLeave';
                                    
                                    $tableHeaderList=array_values(NameMap::$RELIEF_EDIT[$teacherKey]['display']);
                                    if ($isTemp) $tableHeaderList=array_unique ($tableHeaderList);
                                    array_unshift($tableHeaderList, '', '');
                                    if (!$isTemp) $tableHeaderList[]='Verified';
                                    
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
                            <?php 
                                $teacherList=$isTemp? Teacher::getTempTeacher($_SESSION['scheduleDate']) : Teacher::getTeacherOnLeave($_SESSION['scheduleDate']);
                                PageConstant::escapeHTMLEntity($teacherList);                                
                                $keyList=array_keys(NameMap::$RELIEF_EDIT[$teacherKey]['display']);
                                $keyExtraList=NameMap::$RELIEF_EDIT[$teacherKey]['hidden'];
                                
                                $teacherVerifiedList=$_SESSION['teacherVerified'];
                                
                                // construct reason option array and time option array
                                $reasonArr=NameMap::$RELIEF['leaveReason']['display'];
                                $motherTongueArr=NameMap::$RELIEF['MT']['display'];
                                
                                $numOfTeacher=count($teacherList);
                                for ($i=0; $i<$numOfTeacher; $i++)
                                {
                                    $teacher=$teacherList[$i];

                                    if (!$isTemp)
                                    {
                                        $datetime=$teacher[$keyList[2]];
                                        $reasonOptionStr=PageConstant::formatOptionInSelect($reasonArr, $teacher[$keyList[1]]);
                                        $remarkStr=$teacher[$keyList[3]];
                                        $verifiedStr=PageConstant::stateRepresent($teacherVerifiedList[$teacher[$keyExtraList[1]]]);
                                        
                                        $nameTimeInBetweenFrag= <<< EOD
<td>
    <span class="toggle-display">{$reasonArr[$teacher[$keyList[1]]]}</span>
    <select name="reason-$i" class="toggle-edit">$reasonOptionStr</select>
</td>
EOD;
    
                                        $verifiedFrag= <<< EOD
<td>$verifiedStr <input type="hidden" name="leaveID-$i" value="{$teacher[$keyExtraList[1]]}" /></td>   
EOD;
                                    }
                                    else
                                    {
                                        $datetime=$teacher[$keyList[4]];                                        
                                        $motherTongueOptionStr=PageConstant::formatOptionInSelect($motherTongueArr, $teacher[$keyList[3]]);
                                        $remarkStr=$teacher[$keyList[5]];
                                        
                                        $nameTimeInBetweenFrag= <<< EOD
<td>
    <div class="toggle-display">{$teacher[$keyList[1]]}<br />{$teacher[$keyList[2]]}</div>
    <div class="toggle-edit">
        <div class="time-line"><input type="text" name="phone-$i" value="{$teacher[$keyList[1]]}" /></div>
        <div class="time-line"><input type="text" name="email-$i" value="{$teacher[$keyList[2]]}" /></div>
    </div>
</td>
<td>
    <span class="toggle-display">{$motherTongueArr[$teacher[$keyList[3]]]}</span>
    <select name="MT-$i" class="toggle-edit">$motherTongueOptionStr</select>
</td>
EOD;
                                    }
                                    
                                    $timeFromOptionStr=PageConstant::formatOptionInSelect(SchoolTime::getTimeArrSub(0, -1), $datetime[0][1], true);
                                    $timeToOptionStr=PageConstant::formatOptionInSelect(SchoolTime::getTimeArrSub(1, 0), $datetime[1][1], true);                                    
                                    echo <<< EOD
<tr>
    <td><a href="" class="edit-bt small-bt"></a><a href="" class="delete-bt small-bt"></a></td>
    <td><input type="checkbox" name="select-$i" /></td>
    <td>{$teacher[$keyList[0]]} <input type="hidden" name="accname-$i" value="{$teacher[$keyExtraList[0]]}" /></td>
    $nameTimeInBetweenFrag
    <td>
        <div class="toggle-display"><span>{$datetime[0][0]}</span> <span>{$datetime[0][1]}</span><br /><span>{$datetime[1][0]}</span> <span>{$datetime[1][1]}</span></div>
        <div class="toggle-edit">
            <div class="time-line">From: <input type="text" name="date-from-$i" maxlength="10" style="width: 7em; margin-right: 5px" /><input type="hidden" name="server-date-from-$i" value="{$datetime[0][0]}" />
                <select name="time-from-$i">$timeFromOptionStr</select>
            </div>
            <div class="time-line">To: <input type="text" name="date-to-$i" maxlength="10" style="width: 7em; margin-right: 5px" /><input type="hidden" name="server-date-to-$i" value="{$datetime[1][0]}" />
                <select name="time-to-$i">$timeToOptionStr</select>
            </div>
        </div>
    </td>
    <td><div class="toggle-display">$remarkStr</div><textarea name="remark-$i" class="toggle-edit">$remarkStr</textarea></td>
    $verifiedFrag
</tr>
EOD;
                                }
                                
                                include 'teacher-edit-frag.php';
                            ?>                            
                        </tbody>
                    </table>
                </div>
                <div class="bt-control">
                	<input type="button" name="verify" value="Verify Selected" class="button" />
                    <input type="button" name="delete" value="Delete Selected" class="button" />
                </div>
                <input type="hidden" name="num" value="<?php echo count($teacherList); ?>" />
            </form>
            <div id="dialog-confirm"></div>
        </div>        
    </div>
    <?php include '../sidebar-frag.php'; ?>
</div>
    
</body>
</html>
