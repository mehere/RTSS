<?php
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

$isTemp=$_GET['teacher'] == 'temp';

Template::printHeaderAndDoValidation('Scheduler (Edit)', 
        array('relief.css', 'relief-edit.css'),
        array("teacher-detail.js", 'relief-edit.js'), 
        Template::HOME, $isTemp ? "Edit Temporary Relief" : "Edit Leave", Template::SCHEDULE);
?>
<form class="main" name="edit" action="_teacher_edit.php" method="post">            	
    <input type="hidden" name="prop" value="<?php echo $isTemp?'temp':'leave'; ?>" />
    <div class="accordion colorbox blue">
        <span class="box-title">
            <?php echo $isTemp ? "Temporary Relief Teacher:" : "Teacher on Leave:"; ?>
        </span>
        <div class="control-top"><a id="select-all" href="">Select All</a><a id="deselect-all" href="">Deselect All</a></div>
    </div>
    <div>        
        <table class="hovered table-info">
            <thead>
                <tr>
                    <?php                                 
                        $width=$isTemp ? array('45px', '40px', '21%', '33%',  '245px', '46%') : 
//                                        array('45px', '40px', '40%', '170px', '235px', '60%', '70px');
                            array('45px', '40px', '40%', '170px', '245px', '60%');

                        $teacherKey=$isTemp? 'tempTeacher' : 'teacherOnLeave';

                        $tableHeaderList=array_values(NameMap::$RELIEF_EDIT[$teacherKey]['display']);
                        if ($isTemp) $tableHeaderList=array_unique($tableHeaderList);
                        array_unshift($tableHeaderList, '', '');
//                                    if (!$isTemp) $tableHeaderList[]='Verified';

                        for ($i=0; $i<count($tableHeaderList); $i++)
                        {
                            echo <<< EOD
                                <th class="hovered" style="width: $width[$i]">$tableHeaderList[$i]</th>
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

//                    $teacherVerifiedList=$_SESSION['teacherVerified'];

                    // construct reason option array and time option array
                    $reasonArr=NameMap::$RELIEF['leaveReason']['display'];
//                    $motherTongueArr=NameMap::$RELIEF['MT']['display'];

                    $numOfTeacher=count($teacherList);
                    for ($i=0; $i<$numOfTeacher; $i++)
                    {
                        $teacher=$teacherList[$i];
                        $verifiedFrag='';

                        $teacherName="{$teacher[$keyList[0]]}";
                        if (!$isTemp)
                        {
                            $teacherName=<<< EOD
<a class="teacher-detail-link" href="_teacher_detail.php?accname={$teacher[$keyExtraList[0]]}">{$teacher[$keyList[0]]}</a>   
EOD;
                            $datetime=$teacher[$keyList[2]];
                            $reasonOptionStr=PageConstant::formatOptionInSelect($reasonArr, $teacher[$keyList[1]]);
                            $remarkStr=$teacher[$keyList[3]];
//                                        $verifiedStr=PageConstant::stateRepresent($teacherVerifiedList[$teacher[$keyExtraList[1]]]);

                            $nameTimeInBetweenFrag= <<< EOD
<td>
<span class="toggle-display">{$reasonArr[$teacher[$keyList[1]]]}</span>
<select name="reason-$i" class="toggle-edit">$reasonOptionStr</select>    
</td>
EOD;

//                                        $verifiedFrag= <<< EOD
//<td>$verifiedStr</td>   
//EOD;
                        }
                        else
                        {
                            $teacher['leaveID']=$teacher['availability_id'];
                            unset($teacher['availability_id']);

                            $datetime=$teacher['datetime'];                                        
//                            $motherTongueOptionStr=PageConstant::formatOptionInSelect($motherTongueArr, $teacher[$keyList[3]]);
                            $remarkStr=$teacher[$keyList[4]];

                            $nameTimeInBetweenFrag= <<< EOD
<td>
<div class="toggle-display"><span>{$teacher[$keyList[1]]}</span><br /><span>{$teacher[$keyList[2]]}</span></div>
<div class="toggle-edit">
<div class="time-line"><input type="text" name="handphone-$i" value="{$teacher['handphone']}" class="textfield" /></div>
<div class="time-line"><input type="text" name="email-$i" value="{$teacher['email']}" class="textfield" /></div>
</div>
</td>
EOD;
//<td>
//<span class="toggle-display">{$motherTongueArr[$teacher[$keyList[3]]]}</span>
//<select name="MT-$i" class="toggle-edit">$motherTongueOptionStr</select>
//</td>
                        }

                        $dateFromDisplay=SchoolTime::convertDate($datetime[0][0]);
                        $dateToDisplay=SchoolTime::convertDate($datetime[1][0]);

                        $timeFromOptionStr=PageConstant::formatOptionInSelect(SchoolTime::getTimeArrSub(0, -2), $datetime[0][1], true);
                        $timeToOptionStr=PageConstant::formatOptionInSelect(SchoolTime::getTimeArrSub(1, -1), $datetime[1][1], true);                                    
                        echo <<< EOD
<tr>
<td><a href="" class="edit-bt small-bt"></a><a href="" class="delete-bt small-bt"></a></td>
<td><input type="checkbox" name="select-$i" /></td>
<td>$teacherName <input type="hidden" name="accname-$i" value="{$teacher[$keyExtraList[0]]}" /><input type="hidden" name="leaveID-$i" value="{$teacher[$keyExtraList[1]]}" /></td>
$nameTimeInBetweenFrag
<td>
<div class="toggle-display"><span>$dateFromDisplay</span>, <span>{$datetime[0][1]}</span><br /><span>$dateToDisplay</span>, <span>{$datetime[1][1]}</span></div>
<div class="toggle-edit">
<div class="time-line">From: <input type="text" name="date-from-$i" maxlength="10" style="width: 7em; margin-right: 5px" /><input type="hidden" name="server-date-from-$i" value="{$datetime[0][0]}" />
    <select name="time-from-$i">$timeFromOptionStr</select>
</div>
<div class="time-line">To: <input type="text" name="date-to-$i" maxlength="10" style="width: 7em; margin-right: 5px" /><input type="hidden" name="server-date-to-$i" value="{$datetime[1][0]}" />
    <select name="time-to-$i">$timeToOptionStr</select>
</div>
</div>
</td>
<td><div class="toggle-display">$remarkStr</div><div><textarea name="remark-$i" class="toggle-edit">$remarkStr</textarea></div></td>
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
<!--                	<input type="button" name="verify" value="Verify Selected" class="button" />-->
        <input type="button" name="goback" value="Go Back" class="button" />
        <input type="button" name="delete" value="Delete Selected" class="button red" />
    </div>
    <div style="clear: both"></div>
    <input type="hidden" name="num" value="<?php echo count($teacherList); ?>" />
</form>
<div id="teacher-detail"></div>
<div id="dialog-alert"></div>
<?php
Template::printFooter();
?>