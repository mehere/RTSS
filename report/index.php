<?php
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

Template::printHeaderAndDoValidation('Report', 
        array('relief.css', 'report.css'), 
        array("teacher-detail.js", 'report.js'), 
        Template::REPORT, 'Report (Overall)', Template::REPORT_OVERALL);

$year=$_POST['year'] ? $_POST['year'] : SchoolTime::getSemYearFromDate(1);
$sem=$_POST['sem'] ? $_POST['sem'] : SchoolTime::getSemYearFromDate();
?>
<form class="row" name="report-overall" method="post">
    <span class="label">Year:</span>
    <select name="year">
        <?php echo PageConstant::printYearRange($year); ?>
    </select>
    <span class="label">Sem:</span>
    <select name="sem">
        <?php echo PageConstant::printSemRange($sem); ?>
    </select>
    <span class="label">Type:</span>
    <select name="type">
        <optgroup label="-- Filter --">
            <option value="">Any</option>
            <?php echo PageConstant::formatOptionInSelect(NameMap::$REPORT['teacherType']['display'], $_POST['type']) ?>
        </optgroup>                    
    </select>
    <input type="submit" class="button button-small" value="Go" style="margin-left: 30px" />
    <input type="hidden" name="order" value="<?php echo $_POST['order'] ?>" />
    <input type="hidden" name="direction" value="<?php echo $_POST['direction'] ?>" />
</form>
<div class="accordion colorbox blue">    
    <span class="box-title">
        Overall        
    </span>
    <div class="control-top"><a href="print.php" target="_blank" id="print">Print</a></div>    
</div>
<div id="overall">
    <table class="hovered table-info">
        <thead>
            <tr>
                <?php
                    $width=array('100%', '90px', '80px', '150px', '80px', '70px');
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
                $reportArr=ReportDB::getReasonList($_POST['type'], $year, $sem, $_POST['order']?$_POST['order']:"fullname", $_POST['direction']==2 ? SORT_DESC : SORT_ASC);
                PageConstant::escapeHTMLEntity($reportArr);

                foreach ($reportArr as $value)
                {
                    $net=PageConstant::calculateNet($value['numOfMC'], $value['numOfRelief']);
                    
                    $reason='';
                    arsort($value['reason']);
                    foreach ($value['reason'] as $reasonKey => $reasonValue)
                    {                        
                        $reason .= NameMap::$RELIEF['leaveReason']['display'][$reasonKey] . ": $reasonValue<br />";
                    }
                    
                    echo <<< EOD
<tr><td><a href="/RTSS2/relief/_teacher_detail.php?accname={$value['accname']}" class="teacher-detail-link">{$value['fullname']}</a></td><td>{$value['type']}</td><td>{$value['numOfMC']}</td><td>$reason</td><td>{$value['numOfRelief']}</td><td>$net</td></tr>   
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