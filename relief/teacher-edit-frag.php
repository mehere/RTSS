<?php 
require_once '../constant.php';

if (!$reasonArr)
{
    $reasonArr=NameMap::$RELIEF['leaveReason']['display'];
    $timeArr=array();
    for ($i=PageConstant::$SCHOOL_START_TIME; $i<=PageConstant::$SCHOOL_END_TIME; $i+=PageConstant::SCHOOL_TIME_INTERVAL*60)
    {
        $timeStr=date("H:i", $i);
        $timeArr[$timeStr]=$timeStr;
    }
}

if (!$reasonOptionStr) $reasonOptionStr=PageConstant::formatOptionInSelect($reasonArr, '');
if (!$timeFromOptionStr) $timeFromOptionStr=PageConstant::formatOptionInSelect($timeArr, '');
if (!$timeToOptionStr) $timeToOptionStr=PageConstant::formatOptionInSelect($timeArr, '');

if ($_GET['num']) $numOfTeacher=$_GET['num'];

echo <<< EOD
<tr id="last-row">
    <td><div class="add-edit"><a href="" class="edit-bt small-bt"></a><a href="" class="delete-bt small-bt"></a></div></td><td></td>
    <td><input type="text" name="fullname-$numOfTeacher" style="width: 90%;" class="fullname-server" /><input type="hidden" name="accname-$numOfTeacher" value="" /></td>
    <td>
        <select name="reason-$numOfTeacher">$reasonOptionStr
        </select>
    </td>
    <td>
        <div class="time-line">From: <input type="text" name="date-from-$numOfTeacher" maxlength="10" style="width: 7em; margin-right: 5px" />
            <select name="time-from-$numOfTeacher">$timeFromOptionStr</select>
        </div>
        <div class="time-line">To: <input type="text" name="date-to-$numOfTeacher" maxlength="10" style="width: 7em; margin-right: 5px" />
            <select name="time-to-$numOfTeacher">$timeToOptionStr</select>
        </div>
    </td>
    <td><textarea name="remark-$numOfTeacher"></textarea></td><td></td>
</tr>
EOD;
?>