<?php 
$BYPASS_ADMIN=true;
include_once '../php-head.php';

$isAdmin=false;
if ($_SESSION['type'] == 'admin')
{
    $isAdmin=true;
}

function serializeTd($colKey, $input)
{
    $output="";
    foreach ($colKey as $value)
    {
        $output .= PageConstant::tdWrap($input[$value]);
    }    
    
    return $output;
}

require_once '../class/SMSDB.php';

include_once '../head-frag.php';
?>
<title><?php echo PageConstant::SCH_NAME_ABBR . " " . PageConstant::PRODUCT_NAME; ?></title>

<link href="/RTSS/jquery-ui/ui-lightness/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css" />
<script src="/RTSS/jquery-ui/jquery-ui-1.9.2.custom.min.js"></script> 

<link href="/RTSS/css/main.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/relief.css" rel="stylesheet" type="text/css" />
<style type="text/css">
.table-info {
    width: 100%;
}
</style>
<script src="/RTSS/js/sms.js"></script>
</head>
<body>

<div id="container">
    <div id="content-wrapper">
        <div id="content">
            <?php
            $TOPBAR_LIST=array(
                array('tabname' => 'SMS', 'url' => "/RTSS/sms/"),
                array('tabname' => 'Status', 'url' => ""),
            );
            include '../topbar-frag.php';
            ?>
            <div class="main">
                <form name="console" action="" method="post">
                    <?php     
                        $date=$_POST['date'];
                        if (!$date)
                        {
                            $date=$_SESSION['scheduleDate'];
                        }
                    ?>
                    <div class="line"><span>Date:</span> <input type="text" class="textfield" name="date-display" style="width:6.5em" maxlength="10" /><input type="hidden" name="date" value="<?php echo $date; ?>" /> <img id="calendar-trigger" src="/RTSS/img/calendar.gif" alt="Calendar" style="vertical-align: middle; cursor: pointer" />
                    </div>
                    <div class="section">
                        SMS for Relief Alert:
                        <table class="table-info">
                            <thead>
                                <tr>
                                    <?php
                                    $width=array('60px', '40%', '90px', '100px', '80px', '60%');

                                    $tableHeaderList=NameMap::$SMS['layout']['display'];

                                    $i=0;
                                    foreach ($tableHeaderList as $key => $value)
                                    {
                                        echo <<< EOD
                                            <th style="width: $width[$i]" class="sort" search="$key" direction="-1">$value<span class="ui-icon ui-icon-arrowthick-2-n-s"></span></th>                                            
EOD;
                                        $i++;
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $smsList=SMSDB::allSMSStatus($date);
//                                array(array('sentTime'=>'12:00', 'fullname'=>'Armstrong Daniel',
//                                        'phone'=>'98765432', 'status'=>'Invalid serial no', 'repliedTime'=>'23:00',
//                                        'repliedMsg'=>'OK'));
                                    
                                    foreach ($smsList as $smsObj)
                                    {
                                        echo "<tr>" . serializeTd(array_keys($tableHeaderList), $smsObj) . "</tr>";
                                    }
                                    
                                    if (empty($smsList))
                                    {
                                        $otherTdStr=implode('', array_map(array("PageConstant", "tdWrap"), array_fill(0, count($tableHeaderList), '--')));                                            
                                        echo "<tr>$otherTdStr</tr>";
                                    }
                                ?>                               
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>        
    </div>
    <?php include '../sidebar-frag.php'; ?>
</div>
    
</body>
</html>