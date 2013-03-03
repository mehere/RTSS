<?php 
include_once '../../php-head.php';

require_once '../../class/TimetableDB.php';
require_once '../../class/ListGenerator.php';
require_once '../../class/SchedulerDB.php';

$scheduleIndexArr=$_SESSION['scheduleIndex'];
$curScheduleIndex=$scheduleIndexArr[$_GET['schedule']-1];

include_once '../../head-frag.php';
?>
<title><?php echo PageConstant::SCH_NAME_ABBR . " " . PageConstant::PRODUCT_NAME; ?></title>
<link href="/RTSS/css/main.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/timetable.css" rel="stylesheet" type="text/css" />

<link href="/RTSS/jquery-ui/ui-lightness/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css" />
<script src="/RTSS/jquery-ui/jquery-ui-1.9.2.custom.min.js"></script>

<script type="text/javascript">
$(document).ready(function(){
    var formS=document.forms['switch'];
    
    $(formS['accname']).change(function(){
        this.form.submit();
    });
});
</script>
</head>
<body>

<div id="container">  	
    <div id="content-wrapper">
    	<div id="content">
            <?php
                $TOPBAR_LIST=array(
                    array('tabname' => 'Scheduling', 'url' => "/RTSS/relief/"),
                    array('tabname' => 'Result Approval', 'url' => "/RTSS/relief/schedule/result.php?result={$_GET['schedule']}"),
                    array('tabname' => 'Result Preview', 'url' => "")
                );
                include '../../topbar-frag.php';
            ?>
            <div class="main">
                <div style="text-align: center; font-size: 1.2em">Schedule Result Choice <?php echo $_GET['schedule']; ?></div>
                <form name="switch" class="control" action="" method="post">
                    <div class="line">
                        <select name="accname">
                            <option value="">-- Select a Teacher --</option>
                            <?php echo PageConstant::formatOptionInSelect(ListGenerator::getTeacherName($_SESSION['scheduleDate'], $curScheduleIndex), $_POST['accname']); ?>
                        </select>                        
                    </div>
                </form>
                <?php
                    $timetable=TimetableDB::getReliefTimetable('', '', $_SESSION['scheduleDate'], $curScheduleIndex);
                    PageConstant::escapeHTMLEntity($timetable);

                    $timetableIndividual=TimetableDB::getIndividualTimetable($_SESSION['scheduleDate'], $_POST['accname'], $curScheduleIndex);
                    PageConstant::escapeHTMLEntity($timetableIndividual);
                    
                    include '../../timetable/relief-timetable-frag.php';
                ?>
            </div>
            <div class="bt-control">
                <a href="result.php?result=<?php echo $_GET['schedule']; ?>" class="button">Go Back</a>
            </div>
        </div>        
    </div>
    <?php include '../../sidebar-frag.php'; ?>
</div>
    
</body>
</html>