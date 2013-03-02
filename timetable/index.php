<?php 
$BYPASS_ADMIN=true;
include_once '../php-head.php';

$isAdmin=false;
if ($_SESSION['type'] == 'admin')
{
    $isAdmin=true;
}

require_once '../class/ListGenerator.php';
require_once '../class/TimetableDB.php';

include_once '../head-frag.php';
?>
<title><?php echo PageConstant::SCH_NAME_ABBR . " " . PageConstant::PRODUCT_NAME; ?></title>

<link href="/RTSS/jquery-ui/ui-lightness/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css" />
<script src="/RTSS/jquery-ui/jquery-ui-1.9.2.custom.min.js"></script> 

<link href="/RTSS/css/main.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/timetable.css" rel="stylesheet" type="text/css" />
<script src="/RTSS/js/timetable.js"></script>

</head>
<body>

<div id="container">
    <div id="content-wrapper">
        <div id="content">
            <?php
            $TOPBAR_LIST=array(
                array('tabname' => 'Timetable', 'url' => "/RTSS/timetable/"),
                array('tabname' => 'View', 'url' => ""),
            );
            include '../topbar-frag.php';
            ?>
            <div class="main">
                <form name="switch" class="control" action="" method="post">
                    <?php
                        $class=$_POST['class'];
                        $accname=$isAdmin?$_POST['accname']:$_SESSION['accname'];

                        $date=$_POST['date'];
                        if (!$date)
                        {
                            $date=$_SESSION['scheduleDate'];
                        }
                    ?>
                    <div class="line"><span>Date:</span> <input type="text" class="textfield" name="date-display" maxlength="10" /><input type="hidden" name="date" value="<?php echo $date; ?>" /> <img id="calendar-trigger" src="/RTSS/img/calendar.gif" alt="Calendar" style="vertical-align: middle; cursor: pointer" />
                    </div>
                    <?php if ($isAdmin) { ?>
                        <div class="line">                        
                            <select name="accname">
                                <option value="">-- Select a Teacher --</option>
                                <?php echo PageConstant::formatOptionInSelect(ListGenerator::getTeacherName($date), $_POST['accname']); ?>
                            </select>                        
                        </div>
                    <?php } ?>
                </form>
                <?php
                    if ($isAdmin) 
                    {
                        $timetable=TimetableDB::getReliefTimetable('', '', $date);
                        PageConstant::escapeHTMLEntity($timetable);
                    }
                    
                    $timetableIndividual=TimetableDB::getIndividualTimetable($date, $accname);
                    PageConstant::escapeHTMLEntity($timetableIndividual);
                    
                    $NO_PREIVEW=true;
                    include 'relief-timetable-frag.php'; 
                ?>
            </div>
        </div>        
    </div>
    <?php include '../sidebar-frag.php'; ?>
</div>
    
</body>
</html>