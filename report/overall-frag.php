<?php
require_once '../constant.php';
?>

<form class="main" name="report-overall" method="post">
    <fieldset>
        <legend>Filter</legend>
        <div class="line">
            Type: <select name="type"><?php echo PageConstant::formatOptionInSelect(array_merge(NameMap::$REPORT['teacherType']['hidden'], NameMap::$REPORT['teacherType']['display']), '') ?></select>
            <input type="submit" value="Go" class="button" style="margin-left: 30px" />
        </div>        
    </fieldset>
    <div class="section">
        <table class="table-info">
            <thead>
                <tr>
                    <?php
                        $width=array('100%', '90px', '80px', '80px', '80px');
                        $tableHeaderList=array_values(NameMap::$REPORT['overall']['display']);

                        for ($i=0; $i<count($tableHeaderList); $i++)
                        {
                            echo <<< EOD
                                <th style="width: $width[$i]" class="sort">$tableHeaderList[$i]<!--span class="ui-icon ui-icon-arrowthick-2-n-s"></span--></th>
EOD;
                        }
                    ?>
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