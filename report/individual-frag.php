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
        <table class="table-info">
            <tbody id="table-individual">
                <tr>
                    <?php 
                        foreach (array('numOfMC', 'numOfRelief', 'net') as $headerKey) 
                        {
                            echo <<< EOD
<th>{$headerArr[$headerKey]}</th><td>{$teacher[$headerKey]}</td>
EOD;
                        }
                    ?>
                </tr>
                <tr>
                    <?php 
                        foreach (array('mc', 'relief') as $headerKey) 
                        {
                            echo <<< EOD
<th>{$headerArr[$headerKey]}</th><td colspan="2">{$teacher[$headerKey]}</td>
EOD;
                        }
                    ?>
                </tr>        
            </tbody>
        </table>
    </div>               
</form>
