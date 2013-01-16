<?php 
if (!$reasonOptionStr) $reasonOptionStr=PageConstant::formatOptionInSelect($reasonArr, '');
if (!$timeFromOptionStr) $timeFromOptionStr=PageConstant::formatOptionInSelect($timeArr, '');

if ($_GET['num']) $numOfTeacher=$_GET['num'];

echo <<< EOD
<tr id="last-row">
    <td><div class="add-edit"><a href="" class="edit-bt small-bt"></a><a href="" class="delete-bt small-bt"></a></div></td><td></td>
    <td><input type="text" name="fullname-$numOfTeacher" style="width: 90%;" class="fullname-server" /></td>
    <td>
        <select name="reason-$numOfTeacher"><?php echo $reasonOptionStr; ?>
        </select>
    </td>
    <td>
        <div class="time-line">From: <input type="text" name="date-from" maxlength="10" style="width: 7em; margin-right: 5px" />
            <select name="time-from-$numOfTeacher"><?php echo $timeFromOptionStr; ?></select>
        </div>
        <div class="time-line">To: <input type="text" name="date-to" maxlength="10" style="width: 7em; margin-right: 5px" />
            <select name="time-to-$numOfTeacher"><?php echo $timeFromOptionStr; ?></select>
        </div>
    </td>
    <td><textarea name="remark-$numOfTeacher"></textarea></td><td></td>
</tr>
EOD;
?>