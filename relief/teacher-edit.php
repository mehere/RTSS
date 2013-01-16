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
                                    $width=array('45px', '50px', '30%', '20%', '240px', '50%', '70px');
                                                                        
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
                                $keyList=array_keys(NameMap::$RELIEF_EDIT['teacherOnLeave']['display']);
                                $keyExtraList=NameMap::$RELIEF_EDIT['teacherOnLeave']['hidden'];
                                
                                // construct reason option array and time option array
                                $reasonArr=NameMap::$RELIEF['leaveReason']['display'];
                                $timeArr=array();
                                for ($i=PageConstant::$SCHOOL_START_TIME; $i<=PageConstant::$SCHOOL_END_TIME; $i+=PageConstant::SCHOOL_TIME_INTERVAL*60)
                                {
                                    $timeStr=date("H:i", $i);
                                    $timeArr[$timeStr]=$timeStr;
                                }
                                
                                for ($i=0; $i<count($teacherList); $i++)
                                {
                                    $teacher=$teacherList[$i];
                                    
                                    $datetime=$teacher[$keyList[2]];
                                    $reasonOptionStr=PageConstant::formatOptionInSelect($reasonArr, $teacher[$keyList[1]]);
                                    $timeFromOptionStr=PageConstant::formatOptionInSelect($timeArr, '');
                                    echo <<< EOD
<tr>
    <td><a href="" class="edit-bt small-bt"></a><a href="" class="delete-bt small-bt"></a></td>
    <td><input type="checkbox" name="select-$i" /></td>
    <td>{$teacher[$keyList[0]]} <input type="hidden" name="accname" value="{$teacher[$keyExtraList[0]]}" /></td>
    <td>
        <span class="toggle-display">{$teacher[$keyList[1]]}</span>
        <select name="reason-$i" class="toggle-edit">$reasonOptionStr</select>
    </td>
    <td>
        <div class="toggle-display">2012-12-10 07:15 <br /> 2012-12-11 14:30</div>
        <div class="toggle-edit">
            <div class="time-line">From: <input type="text" name="date-from-$i" maxlength="10" style="width: 7em; margin-right: 5px" /><input type="hidden" name="server-date-from-$i" value="2012-12-10" />
                <select name="time-from-$i">$timeFromOptionStr</select>
            </div>
            <div class="time-line">To: <input type="text" name="date-to-$i" maxlength="10" style="width: 7em; margin-right: 5px" /><input type="hidden" name="server-date-to-$i" value="2012-12-10" />
                <select name="time-to-$i">$timeFromOptionStr</select>
            </div>
        </div>
    </td>
    <td><div class="toggle-display">{$teacher[$keyList[3]]}</div><textarea name="remark-$i" class="toggle-edit">{$teacher[$keyList[3]]}</textarea></td>
    <td><input type="checkbox" name="verified-$i" /></td>
</tr>
EOD;
                                }
/*EOD;
    foreach ($reasonArr as $reason)
    {
        $optionSelectedStr="";
        if ($teacher[$keyList[1]] == $reason) $optionSelectedStr='selected="selected"';
        echo <<< EOD
            <option value="$reason" $optionSelectedStr>$reason</option>
EOD;
    }
    echo <<< EOD*/                               
                            ?>
<!--                        	<tr>
                            	<td><a href="" class="edit-bt small-bt"></a><a href="" class="delete-bt small-bt"></a></td>
                            	<td><input type="checkbox" name="select-0" /></td>
                                <td><a href="_teacher_detail.php?accname=1234" class="teacher-detail-link">haha asdf</a></td>
                            	<td>
                                	<span class="toggle-display">MC</span>
                                	<select name="reason" class="toggle-edit"><option value="MC">MC</option>
                                    </select>
                                </td>
                            	<td>
                                	<div class="toggle-display">2012-12-10 07:15 <br /> 2012-12-11 14:30</div>
                                	<div class="toggle-edit">
                                        <div class="time-line">From: <input type="text" name="date-from-0" maxlength="10" style="width: 7em; margin-right: 5px" /><input type="hidden" name="server-date-from-0" value="2012-12-10" />
                                            <select name="time-from-0"><option value="15">07:15</option></select>
                                        </div>
                                        <div class="time-line">To: <input type="text" name="date-to-0" maxlength="10" style="width: 7em; margin-right: 5px" /><input type="hidden" name="server-date-to-0" value="2012-12-10" />
                                            <select name="time-to-0"><option value="15">14:30</option></select>
                                        </div>
                                    </div>
                                </td>
                                <td><div class="toggle-display">Specifies that a text area should automatically</div><textarea name="remark-0" class="toggle-edit">Specifies that a text area should automatically</textarea></td><td><input type="checkbox" name="verified-0" /></td>
                            </tr>-->
                            <tr id="last-row"><td></td>
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
        </div>        
    </div>
    <?php include '../sidebar-frag.php'; ?>
</div>
    
</body>
</html>