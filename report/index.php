<?php 
include_once '../php-head.php';

function tdWrap($ele)
{
    return "<td>$ele</td>";
}

include_once '../head-frag.php';
?>
<title><?php echo PageConstant::SCH_NAME_ABBR . " " . PageConstant::PRODUCT_NAME; ?></title>

<link href="/RTSS/jquery-ui/ui-lightness/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css" />
<script src="/RTSS/jquery-ui/jquery-ui-1.9.2.custom.min.js"></script>

<link href="/RTSS/css/main.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/tab.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/relief.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/report.css" rel="stylesheet" type="text/css">
<script src="/RTSS/js/jquery.cookie.js"></script>
<script src="/RTSS/js/report.js"></script>
<script src="/RTSS/js/teacher-detail.js"></script>

<!--[if lt IE 9]>
<style type="text/css">
#tabs .ie8-tab-border {
    border: 1px solid #c8c8c8;
}
</style>
<![endif]-->

</head>
<body>

<div id="container">  	
    <div id="content-wrapper">
    	<div id="content">
            <?php 
                $TOPBAR_LIST=array(
                    array('tabname'=>'Report', 'url'=>"/RTSS/report/"), 
                    array('tabname'=>'View', 'url'=>""), 
                );
                include '../topbar-frag.php';
            ?>
            <div id="tabs">            	
                <ul>
                    <li><a href="#tabs-1">Overall</a></li>
                    <li><a href="#tabs-2">Individual</a></li>
                </ul>
                <div id="tabs-1" class="ie8-tab-border">
                    <div class="gradient-top-fill"></div>
                    <div class="gradient-top"></div>
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
                    <div id="teacher-detail">Loading ...</div>                    
                </div>
                <div id="tabs-2" class="ie8-tab-border">
                    <div class="gradient-top-fill"></div>
                    <div class="gradient-top"></div>
                    <form class="main" name="report-individual" method="post">
                        <fieldset>
                            <legend>Enter</legend>
                            <div class="line">
                                Name: <input type="text" name="fullname" class="textfield" />
                                <input type="hidden" name="accname" />
                                <input type="submit" value="Go" class="button" />
                            </div>            
                        </fieldset>
                        <div class="section">
                            <table class="table-info" id="individual-summary">
                                <tbody>
                                    <?php
                                        // $_POST['accname']
                                        $teacher=array('numOfMC' => 4, 'numOfRelief' => 3, 
                                            'mc'=>array(array(array('2012/12/12', '11:45'), array('2012/12/13', '10:45')), array(array('2013/1/12', '13:45'), array('2013/1/13', '08:45'))),
                                            'relief'=>''
                                        );
                                        $teacher['net']=PageConstant::calculateNet($teacher['numOfMC'], $teacher['numOfRelief']);
                                        $headerArr=NameMap::$REPORT['individual']['display'];
                                        
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

                </div>
            </div>            
        </div>        
    </div>
    <?php include '../sidebar-frag.php'; ?>
</div>
    
</body>
</html>