<?php
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

Template::printHeaderAndDoValidation('Report', 
        array('relief.css', 'report.css'), 
        array("teacher-detail.js", "accordion.js", 'report.js'), 
        Template::REPORT, 'Report');

$ACCORDION_EXPAND='accordion-expand';
?>
<form class="accordion colorbox blue" name="report-overall" method="post">
    <a href="" class="icon-link <?php if ($_POST['expand'] == 'overall') echo $ACCORDION_EXPAND; ?>"><img src="/RTSS/img/minus-white.png" /><img src="/RTSS/img/plus-white.png" style="display: none" /></a>
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
    <input type="hidden" name="expand" value="overall" />
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
<form class="accordion colorbox green" name="report-individual" method="post">
    <a href="" class="icon-link <?php if ($_POST['expand'] == 'individual') echo $ACCORDION_EXPAND; ?>"><img src="/RTSS/img/minus-white.png" /><img src="/RTSS/img/plus-white.png" style="display: none" /></a>
    <span class="box-title">
        Individual
        <span class="filter-control">
            <input type="text" name="fullname" value="<?php echo $_POST['fullname']; ?>" class="field" />
            <input type="hidden" name="accname" value="<?php echo $_POST['accname']; ?>" />
            <input type="submit" value="Go" class="button" style="font-size: 12px; padding: 5px 10px" />
        </span>
    </span>
    <input type="hidden" name="expand" value="individual" />
</form>                
<div id="individual">
    <table class="hovered table-info" id="individual-summary">
        <tbody>
            <?php                                        
                $teacher=Teacher::individualReport($_POST['accname']);                                        
                $teacher['net']=PageConstant::calculateNet($teacher['numOfMC'], $teacher['numOfRelief']);
                if (!$_POST['accname'])
                {
                    $teacher['net']=$teacher['numOfMC']=$teacher['numOfRelief']='';
                }

                $headerArr=NameMap::$REPORT['individual']['display'];                                        
                echo '<tr>';
                foreach (array('numOfMC', 'numOfRelief', 'net') as $headerKey) 
                {
                    echo <<< EOD
<th class="hovered">{$headerArr[$headerKey]}</th><td>{$teacher[$headerKey]}</td>
EOD;
                }
                echo '</tr>';
            ?>
        </tbody>
    </table>
    <table class="hovered table-info" id="individual-detail">
        <tbody>
            <?php 
                foreach (array('mc', 'relief') as $headerKey) 
                {
                    if ($teacher[$headerKey])
                    {
                        foreach ($teacher[$headerKey] as $tInd => $record)
                        {
                            echo "<tr>";
                            if ($tInd == 0)
                            {
                                $rowspan=count($teacher[$headerKey]);
                                echo <<< EOD
<th rowspan="$rowspan" class="hovered">{$headerArr[$headerKey]}</th>
EOD;
                            }

                            $dateFromDisplay=SchoolTime::convertDate($record[0][0]);
                            $dateToDisplay=SchoolTime::convertDate($record[1][0]);
                            echo <<< EOD
<td colspan="3">$dateFromDisplay {$record[0][1]} - $dateToDisplay {$record[1][1]}</td>
EOD;
                            echo "</tr>";
                        }                                    
                    }
                    else
                    {
                        echo <<< EOD
<tr><th class="hovered">{$headerArr[$headerKey]}</th><td colspan="3"></td></tr>
EOD;
                    }

                }
            ?>
        </tbody>
    </table>
</div>
<div style="clear: both"></div>
<div id="teacher-detail">Loading ...</div>
<?php
Template::printFooter();
?>