<?php
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});



$timeFromOptionStr=PageConstant::formatOptionInSelect(SchoolTime::getTimeArrSub(0, -1), '', true);
$timeToOptionStr=PageConstant::formatOptionInSelect(SchoolTime::getTimeArrSub(1, 0), '', true);

if ($_GET['num']) $numOfTeacher=$_GET['num'];

if ($isTemp===false || $_GET['teacher'] != 'temp')
{
    $reasonOptionStr=PageConstant::formatOptionInSelect(NameMap::$RELIEF['leaveReason']['display'], '');
    
    $nameTimeInBetweenFrag= <<< EOD
<td>
    <div class="add-edit">
        <span class="toggle-display"></span>
        <select name="reason-$numOfTeacher" class="toggle-edit">$reasonOptionStr</select>
    </div>    
</td>
EOD;
    
//    $verifiedNotStr=PageConstant::stateRepresent(0);
//    $verifiedFrag= <<< EOD
//<td><div class="add-edit">$verifiedNotStr</div></td>
//EOD;
}
else
{    
    $motherTongueOptionStr=PageConstant::formatOptionInSelect(NameMap::$RELIEF['MT']['display'], '');

    $nameTimeInBetweenFrag= <<< EOD
<td>
    <div class="add-edit">
        <div class="toggle-display"><span></span><br /><span></span></div>
        <div class="toggle-edit">
            <div class="time-line"><input type="text" name="handphone-$numOfTeacher" class="textfield" /></div>
            <div class="time-line"><input type="text" name="email-$numOfTeacher" class="textfield" /></div>
        </div>
    </div>
</td>
<td>
    <div class="add-edit">
        <span class="toggle-display"></span>
        <select name="MT-$numOfTeacher" class="toggle-edit">$motherTongueOptionStr</select>
    </div>
</td>
EOD;
}

$date=date(PageConstant::DATE_FORMAT_ISO);
echo <<< EOD
<tr id="last-row">
    <td><div class="add-edit"><a href="" class="edit-bt small-bt"></a><a href="" class="delete-bt small-bt"></a></div></td>
    <td><div class="add-edit"><input type="checkbox" name="select-$numOfTeacher" /></div></td>
    <td><input type="text" name="fullname-$numOfTeacher" style="width: 90%; margin: 15px 0" class="fullname-server" /><input type="hidden" name="accname-$numOfTeacher" /><input type="hidden" name="leaveID-$numOfTeacher" /></td>
    $nameTimeInBetweenFrag
    <td>
    	<div class="add-edit">
            <div class="toggle-display"><span></span>, <span></span><br /><span></span>, <span></span></div>
            <div class="toggle-edit">
                <div class="time-line">From: <input type="text" name="date-from-$numOfTeacher" maxlength="10" style="width: 7em; margin-right: 5px" /><input type="hidden" name="server-date-from-$numOfTeacher" value="$date" />
                    <select name="time-from-$numOfTeacher">$timeFromOptionStr</select>
                </div>
                <div class="time-line">To: <input type="text" name="date-to-$numOfTeacher" maxlength="10" style="width: 7em; margin-right: 5px" /><input type="hidden" name="server-date-to-$numOfTeacher" value="$date" />
                    <select name="time-to-$numOfTeacher">$timeToOptionStr</select>
                </div>
            </div>
        </div>
    </td>
    <td><div class="add-edit"><span class="toggle-display"></span><textarea name="remark-$numOfTeacher" class="toggle-edit"></textarea></div></td>
    $verifiedFrag
</tr>
EOD;
?>