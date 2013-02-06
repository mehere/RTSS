<?php
require_once '../constant.php';

// $_POST['accname']
$teacher=array('numOfMC' => 4, 'numOfRelief' => 3, 
    'mc'=>array(array(array('2012-12-12', '11:45'), array('2012-12-13', '10:45')), array(array('2013-1-12', '13:45'), array('2013-1-13', '08:45'))),
    'relief'=>''
);
$teacher['net']=PageConstant::calculateNet($teacher['numOfMC'], $teacher['numOfRelief']);
$headerArr=NameMap::$REPORT['individual']['display'];
?>
<form class="main" name="report-individual" method="post">
    <fieldset>
        <legend>Enter</legend>
        <div class="line">
            Name: <input type="text" name="fullname" style="width: 150px; margin-right: 20px" />
            <input type="hidden" name="accname" />
            <input type="submit" value="Go" class="button" />
        </div>            
    </fieldset>
    <div class="section">
        <table class="table-info" id="individual-summary">
            <tbody>
                <?php 
                    foreach (array('numOfMC', 'numOfRelief', 'net') as $headerKey) 
                    {
                        echo <<< EOD
<tr><th>{$headerArr[$headerKey]}</th><td>{$teacher[$headerKey]}</td></tr>
EOD;
                    }
                ?>
            </tbody>
        </table>
        <table class="table-info" id="individual-detail">
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
<th rowspan="$rowspan">{$headerArr[$headerKey]}</th>
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
<tr><th>{$headerArr[$headerKey]}</th><td colspan="3"></td></tr>
EOD;
                        }

                    }
                ?>
            </tbody>
        </table>
    </div>               
</form>
