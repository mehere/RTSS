<?php 
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

Template::validate();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Report - Print</title>
<link href="/RTSS/css/print.css" rel="stylesheet" type="text/css">
<style type="text/css">
.table-info {
	width: 80%;
}
</style>
</head>

<body>
	<div id="container">
        <h2>
            Report (<?php echo "Sem " . PageConstant::printSemRange(true) . ", " . PageConstant::printYearRange(true); ?>)
            <p style="font-size: 12px">Generated on <?php echo date(PageConstant::DATE_FORMAT_SG); ?></p>
        </h2>        
        <table class="table-info">
            <thead>
                <tr>
                    <?php
                        $width=array('100%', '90px', '80px', '80px', '80px');
                        $tableHeaderArr=NameMap::$REPORT['overall']['display'];

                        $i=0;
                        foreach ($tableHeaderArr as $key => $value)
                        {                                                
                            echo <<< EOD
                                <th style="width: $width[$i]" class="sort" search="$key">$value<span class="ui-icon ui-icon-arrowthick-2-n-s"></span></th>
EOD;
                            $i++;
                        }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                    $reportArr=Teacher::overallReport($_GET['type'], $_GET['order'], $_GET['direction']==2 ? SORT_DESC : SORT_ASC);
                    PageConstant::escapeHTMLEntity($reportArr);

                    foreach ($reportArr as $value)
                    {
                        $net=PageConstant::calculateNet($value['numOfMC'], $value['numOfRelief']);
                        echo <<< EOD
<tr><td>{$value['fullname']}</td><td>{$value['type']}</td><td>{$value['numOfMC']}</td><td>{$value['numOfRelief']}</td><td>$net</td></tr>   
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
</body>
</html>