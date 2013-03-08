<?php
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

Template::printHeaderAndDoValidation('Report', 
        array('report.css'), 
        array('report.js'), 
        Template::REPORT, 'Report (Individual)', Template::REPORT_INDIVIDUAL);

$year=$_POST['year'] ? $_POST['year'] : SchoolTime::getSemYearFromDate(1);
$sem=$_POST['sem'] ? $_POST['sem'] : SchoolTime::getSemYearFromDate();
?>
<form class="row" name="report-individual" method="post">
    <span class="label">Year:</span>
    <select name="year">
        <?php echo PageConstant::printYearRange($year); ?>
    </select>
    <span class="label">Sem:</span>
    <select name="sem">
        <?php echo PageConstant::printSemRange($sem); ?>
    </select>
    <span class="label">Name:</span>
    <span>
    	<input type="text" name="fullname" value="<?php echo $_POST['fullname']; ?>" class="field" />
    	<input type="hidden" name="accname" value="<?php echo $_POST['accname']; ?>" />
    </span>
    <input type="submit" class="button button-small" value="Go" style="margin-left: 30px" />
</form>
<div class="accordion colorbox green">
    <a href="" class="icon-link"></a>
    <span class="box-title">
        Individual
    </span>
</div>                
<div id="individual">
    <table class="hovered table-info" id="individual-summary">
        <tbody>
            <?php           
                $teacher=Teacher::individualReport($_POST['accname'], $year, $sem);                                        
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
<?php
Template::printFooter();
?>