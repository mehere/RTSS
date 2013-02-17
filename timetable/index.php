<?php 
$BYPASS_ADMIN=true;
include_once '../php-head.php';

function tdWrap($ele)
{
    return "<td>$ele</td>";
}

$isAdmin=false;
if ($_SESSION['type'] == 'admin')
{
    $isAdmin=true;
}

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
                        require_once '../class/ListGenerator.php';
                        require_once '../class/TimetableDB.php';
                        
                        $class=$_POST['class'];
                        $teacher=$isAdmin?$_POST['teacher']:$_SESSION['accname'];
                    
                        $date=$_POST['date'];
                        if (!$date)
                        {
                            $date=$_SESSION['scheduleDate'];
                        }
                    ?>
                    <div class="line">Date: <input type="text" class="textfield" name="date-display" maxlength="10" /><input type="hidden" name="date" value="<?php echo $date; ?>" /> <img id="calendar-trigger" src="/RTSS/img/calendar.gif" alt="Calendar" style="vertical-align: middle; cursor: pointer" />
                        <?php if ($isAdmin) { ?>
                        <select name="class" style="margin-left: 30px">
                            <option value="">-- Any --</option>
                            <?php echo PageConstant::formatOptionInSelect(ListGenerator::getClassName($date), $class, true); ?>
                        </select>
                        <select name="teacher">
                            <option value="">-- Any --</option>
                            <?php echo PageConstant::formatOptionInSelect(ListGenerator::getTeacherName($date), $teacher); ?>
                        </select>
                        <?php } ?>
                    </div>
                </form>
                <?php
                    $timetable=TimetableDB::getReliefTimetable($teacher, $class, $date);                    
                    include 'relief-timetable-frag.php'; 
                ?>
            </div>
        </div>        
    </div>
    <?php include '../sidebar-frag.php'; ?>
</div>
    
</body>
</html>