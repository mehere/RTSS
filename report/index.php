<?php
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

Template::printHeaderAndDoValidation('Report', 
        array('relief.css', 'report.css'), 
        array("teacher-detail.js", 'report.js'), 
        Template::REPORT, 'Report (Overall)', Template::REPORT_OVERALL);
?>
<form class="accordion colorbox blue" name="report-overall" method="post">
    <span class="icon-link"></span>
    <span class="box-title">
        Overall
        <span class="filter-control">
            <select name="type">
                <optgroup label="-- Filter --">
                    <option value="">Any</option>
                    <?php echo PageConstant::formatOptionInSelect(NameMap::$REPORT['teacherType']['display'], $_POST['type']) ?>
                </optgroup>                    
            </select>
        </span>
    </span>
    <div class="control-top"><a href="print.php" target="_blank" id="print">Print</a></div>
    <input type="hidden" name="order" />
    <input type="hidden" name="direction" />
</form>
<div id="overall">
    <table class="hovered table-info">
        <thead>
            <tr>
                <?php
                    $width=array('100%', '90px', '80px', '80px', '80px');
                    $tableHeaderArr=NameMap::$REPORT['overall']['display'];

                    $i=0;
                    foreach ($tableHeaderArr as $key => $value)
                    {
                        $dir='';
                        if ($_POST['order'] == $key)
                        {
                            $dir=$_POST['direction'];
                        }
                        echo <<< EOD
                            <th style="width: $width[$i]" class="sort hovered" search="$key" direction="$dir">$value<span class="ui-icon ui-icon-arrowthick-2-n-s"></span></th>
EOD;
                        $i++;
                    }
                ?>
            </tr>
        </thead>
        <tbody>
            <?php
                $reportArr=Teacher::overallReport($_POST['type'], $_POST['order'], $_POST['direction']==2 ? SORT_DESC : SORT_ASC);
                PageConstant::escapeHTMLEntity($reportArr);

                foreach ($reportArr as $value)
                {
                    $net=PageConstant::calculateNet($value['numOfMC'], $value['numOfRelief']);
                    echo <<< EOD
<tr><td><a href="/RTSS/relief/_teacher_detail.php?accname={$value['accname']}" class="teacher-detail-link">{$value['fullname']}</a></td><td>{$value['type']}</td><td>{$value['numOfMC']}</td><td>{$value['numOfRelief']}</td><td>$net</td></tr>   
EOD;
                }

                if (empty($reportArr))
                {
                    $otherTdStr=implode('', array_map(array("PageConstant", "tdWrap"), array_fill(0, count($tableHeaderArr), '--')));                                            
                    echo "<tr>$otherTdStr</tr>";
                }
            ?>
        </tbody>
    </table>    
</div>
<div id="teacher-detail">Loading ...</div>
<?php
Template::printFooter();
?>