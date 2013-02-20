<?php 
include_once '../php-head.php';

function tdWrap($ele)
{
    return "<td>$ele</td>";
}

require_once '../class/Teacher.php';

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
                    <form class="main" name="report-overall" method="post" action="">
                        <fieldset>
                            <legend>Filter</legend>
                            <div class="line">
                                Type: <select name="type"><option value="">Any</option><?php echo PageConstant::formatOptionInSelect(NameMap::$REPORT['teacherType']['display'], $_POST['type']) ?></select>
                                <input type="submit" value="Go" class="button" style="margin-left: 30px" />
                            </div>        
                        </fieldset>
                        <div class="section">
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
                                <tbody id="table-overall">
                                    <?php
                                        $reportArr=Teacher::overallReport($_POST['type'], $_POST['order'], $_POST['direction']==0 ? SORT_ASC : SORT_DESC);
//var_dump(Teacher::overallReport('', 'fullname', SORT_DESC));
//var_dump($_POST['order'], $_POST['direction']);
                                        foreach ($reportArr as $value)
                                        {
                                            $net=PageConstant::calculateNet($value['numOfMC'], $value['numOfRelief']);
                                            echo <<< EOD
<tr><td><a href="/RTSS/relief/_teacher_detail.php?accname={$value['accname']}" class="teacher-detail-link">{$value['fullname']}</a></td><td>{$value['type']}</td><td>{$value['numOfMC']}</td><td>{$value['numOfRelief']}</td><td>$net</td></tr>   
EOD;
                                        }
                                        
                                        if (empty($reportArr))
                                        {
                                            echo '<tr>';
                                            foreach ($tableHeaderArr as $value)
                                            {
                                                echo tdWrap('--');
                                            }
                                            echo '</tr>';
                                        }
                                    ?>
                                </tbody>
                            </table>
                            <input type="hidden" name="order" value="fullname" /><input type="hidden" name="direction" value="<?php echo $_POST['direction'] ?>" />
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
                                Name: <input type="text" name="fullname" class="textfield" value="<?php 
                                    $info=Teacher::getIndividualTeacherDetail($_POST['accname']); 
                                    echo $info['name'];
                                ?>" />
                                <input type="hidden" name="accname" value="<?php echo $_POST['accname']; ?>" />
                                <input type="submit" value="Go" class="button" />
                            </div>            
                        </fieldset>
                        <div class="section">
                            <table class="table-info" id="individual-summary">
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
<th>{$headerArr[$headerKey]}</th><td>{$teacher[$headerKey]}</td>
EOD;
                                        }
										echo '</tr>';
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