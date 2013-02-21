<?php 
include_once '../php-head.php';

require_once '../class/Teacher.php';

include_once '../head-frag.php';
?>
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