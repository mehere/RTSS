<?php 
require_once '../constant.php';

$reasonOptionStr=PageConstant::formatOptionInSelect(NameMap::$RELIEF['leaveReason']['display'], '');
$timeFromOptionStr=PageConstant::formatOptionInSelect(SchoolTime::getTimeArrSub(0, -1), '', true);
$timeToOptionStr=PageConstant::formatOptionInSelect(SchoolTime::getTimeArrSub(1, 0), '', true);

if ($_GET['num']) $numOfTeacher=$_GET['num'];

$verifiedNotStr=PageConstant::stateRepresent(0);

echo <<< EOD
<tr id="last-row">
    <td><div class="add-edit"><a href="" class="edit-bt small-bt"></a><a href="" class="delete-bt small-bt"></a></div></td>
    <td><div class="add-edit"><input type="checkbox" name="select-$numOfTeacher" /></div></td>
    <td><input type="text" name="fullname-$numOfTeacher" style="width: 90%; margin: 15px 0" class="fullname-server" /><input type="hidden" name="accname-$numOfTeacher" value="" /></td>
    <td>
		<div class="add-edit">
            <span class="toggle-display"></span>
            <select name="reason-$numOfTeacher" class="toggle-edit">$reasonOptionStr</select>
        </div>       
    </td>
    <td>
    	<div class="add-edit">
            <div class="toggle-display"><span></span> <span></span><br /><span></span> <span></span></div>
            <div class="toggle-edit">
                <div class="time-line">From: <input type="text" name="date-from-$numOfTeacher" maxlength="10" style="width: 7em; margin-right: 5px" />
                    <select name="time-from-$numOfTeacher">$timeFromOptionStr</select>
                </div>
                <div class="time-line">To: <input type="text" name="date-to-$numOfTeacher" maxlength="10" style="width: 7em; margin-right: 5px" />
                    <select name="time-to-$numOfTeacher">$timeToOptionStr</select>
                </div>
            </div>
        </div>
    </td>
    <td><div class="add-edit"><span class="toggle-display"></span><textarea name="remark-$numOfTeacher" class="toggle-edit"></textarea></div></td>
    <td><div class="add-edit">$verifiedNotStr</div> <input type="hidden" name="leaveID-$numOfTeacher" value="" /></td>
</tr>
EOD;
?>