<?php
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

Template::printHeaderAndDoValidation('View Timetable', 
        array('relief.css', 'timetable.css'), 
        array('teacher-detail.js', 'accordion.js', 'timetable.js'), 
        Template::TT_VIEW, 'Timetable', '', true);

$isAdmin=false;
if ($_SESSION['type'] == 'admin')
{
    $isAdmin=true;
}
?>
<form name="switch" class="control" method="post">
    <?php
        $class=$_POST['class'];
        $accname=$isAdmin?$_POST['accname']:$_SESSION['accname'];

        $date=$_POST['date'];
        if (!$date)
        {
            $date=$_SESSION['scheduleDate'];
        }
        
        $teacherList=PageConstant::formatOptionInSelect(ListGenerator::getTeacherName($date), $_POST['accname']);
    ?>
    <div class="line">Date: <input type="text" class="textfield datefield" name="date-display" maxlength="10" /><input type="hidden" name="date" value="<?php echo $date; ?>" /> <img id="calendar-trigger" src="/RTSS/img/calendar.gif" alt="Calendar" style="vertical-align: middle; cursor: pointer" />
    </div>    
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
    
    echo <<< EOD
<div id="teacher-detail"></div>
EOD;
    
    Template::printFooter();
?>       
