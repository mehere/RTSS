<?php 
include_once '../php-head.php';
    
if (!$_SESSION['accname'])
{
    header("Location: /RTSS/");
}
    
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
            <form class="main" name="edit" action="" method="post">            	
            	<input type="hidden" name="prop" value="<?php echo $isTemp?'temp':'leave'; ?>" />
                <div class="section">
                	<?php echo $isTemp ? "Temporary Relief Teacher:" : "Teacher on Leave:"; ?> 
                    <div class="control-top"><a href="">Select All</a> <a href="">Deselect All</a></div>
                    <table class="table-info">
                        <thead>
                            <tr>
                                <?php                                 
                                    $width=array('45px', '50px', '30%', '170px', '240px', '50%', '70px');
                                                                        
                                    $tableHeaderList=array_values(NameMap::$RELIEF_EDIT[$isTemp ? 'tempTeacher' : 'teacherOnLeave']['display']);
                                    array_unshift($tableHeaderList, '', 'Select');
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
                            <?php 
                                $teacherList=Teacher::getTeacherOnLeave($_SESSION['scheduleDate']);
                                PageConstant::escapeHTMLEntity($teacherList);
                                $keyList=array_keys(NameMap::$RELIEF_EDIT['teacherOnLeave']['display']);
                                $keyExtraList=NameMap::$RELIEF_EDIT['teacherOnLeave']['hidden'];
                                
                                $teacherVerifiedList=$_SESSION['teacherVerified'];
                                
                                // construct reason option array and time option array
                                $reasonArr=NameMap::$RELIEF['leaveReason']['display'];
                                $timeArr=array();
                                for ($i=PageConstant::$SCHOOL_START_TIME; $i<=PageConstant::$SCHOOL_END_TIME; $i+=PageConstant::SCHOOL_TIME_INTERVAL*60)
                                {
                                    $timeStr=date("H:i", $i);
                                    $timeArr[$timeStr]=$timeStr;
                                }
                                
                                $numOfTeacher=count($teacherList);
                                for ($i=0; $i<$numOfTeacher; $i++)
                                {
                                    $teacher=$teacherList[$i];
                                    
                                    $datetime=$teacher[$keyList[2]];
                                    $reasonOptionStr=PageConstant::formatOptionInSelect($reasonArr, $teacher[$keyList[1]]);
                                    $timeFromOptionStr=PageConstant::formatOptionInSelect($timeArr, $datetime[0][1]);
                                    $timeToOptionStr=PageConstant::formatOptionInSelect($timeArr, $datetime[1][1]);
                                    $verifiedStr=PageConstant::stateRepresent($teacherVerifiedList[$teacher[$keyExtraList[0]]]);
                                    echo <<< EOD
<tr>
    <td><a href="" class="edit-bt small-bt"></a><a href="" class="delete-bt small-bt"></a></td>
    <td><input type="checkbox" name="select-$i" /></td>
    <td>{$teacher[$keyList[0]]} <input type="hidden" name="accname-$i" value="{$teacher[$keyExtraList[0]]}" /></td>
    <td>
        <span class="toggle-display">{$teacher[$keyList[1]]}</span>
        <select name="reason-$i" class="toggle-edit">$reasonOptionStr</select>
    </td>
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
    <td><div class="toggle-display">{$teacher[$keyList[3]]}</div><textarea name="remark-$i" class="toggle-edit">{$teacher[$keyList[3]]}</textarea></td>
    <td>$verifiedStr</td>
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