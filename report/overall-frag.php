<?php

$accname=$_POST['accname'];
$type=$_POST['type'];

?>

<form class="main" name="report-overall">
    <fieldset>
        <legend>Filter</legend>
        Name: <input type="text" name="fullname" class="textfield" style="width: 150px; margin-right: 20px" />
        <input type="hidden" name="accname" /> 
        Type: <select name="type"><option value="">Any</option><option value="normal">Normal</option></select>
        <input type="submit" value="Go" class="button" style="margin-left: 30px" />
    </fieldset>
    <div class="section">
        <table class="table-info">
            <thead>
                <tr>
                    <th style="width: 100%" class="sort">Name<!--span class="ui-icon ui-icon-arrowthick-2-n-s"></span--></th>
                    <th style="width: 90px" class="sort">Type</th>
                    <th style="width: 80px" class="sort">MC</th>
                    <th style="width: 80px" class="sort">Relief</th>
                    <th style="width: 80px" class="sort">Net</th>
                </tr>
            </thead>
            <tbody id="table-overall">
                <tr><td><a href="/RTSS/relief/_teacher_detail.php?accname=1234" class="teacher-detail-link">haha asdf</a></td><td>AED</td><td>4</td><td>2</td><td>2</td></tr>
                <tr><td><a href="/RTSS/relief/_teacher_detail.php?accname=cxas">haha xsa</a></td><td>AED</td><td>1</td><td>2</td><td>-1</td></tr>
                <tr><td><a href="/RTSS/relief/_teacher_detail.php?accname=1234">haha asdf</a></td><td>AED</td><td>4</td><td>2</td><td>2</td></tr>
            </tbody>
        </table>
    </div>
</form>
<script src="/RTSS/js/teacher-detail.js"></script>