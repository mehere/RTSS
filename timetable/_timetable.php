<?php
$isTeacher=$_GET['tab'] == 'teacher';
?>
<form name="switch" style="padding: 5px 0 10px">
    <select name="entity">
        <option value="1">Class 1</option>
    </select>
</form>
<table class="table-info">
    <thead>
        <tr>
            <th style="width: 120px">Time</th>
            <th><?php echo $isTeacher?'Teacher':'Class'; ?></th>                                
        </tr>
    </thead>
    <tbody>
        <tr><td>0800 - 0900</td><td>Ana Bob</td></tr>
        <tr><td>0900 - 1000</td><td>XXX Bob (relief for YYY Cad)</td></tr>
    </tbody>
</table>
