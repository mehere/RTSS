<?php
spl_autoload_register(function($class){
    require_once "../../class/$class.php";
});

Template::printHeaderAndDoValidation('Home', 
        array('relief.css', 'page-control.css', 'timetable.css'), 
        array('teacher-detail.js', 'accordion.js'), 
        Template::HOME, Template::HOME . "(Timetable Preview)", Template::SCHEDULE, true);

$curPage=$_GET['schedule'];
if (!$curPage) $curPage=1;

$scheduleIndexArr=$_SESSION['scheduleIndex'];
$scheduleResultNum=count($scheduleIndexArr);

$curScheduleIndex=$scheduleIndexArr[$curPage-1];
?>
<script type="text/javascript">
$(document).ready(function(){
    var formT=document.forms['teacher-select'];
    
    $(formT['accname']).change(function(){
        this.form.submit();
    });
    
    <?php if (!$_POST['accname']) { ?>    
        GlobalFunction.toggleAccordion($('.icon-link', document.forms['teacher-select']), 0);
    <?php } ?>     
});
</script>
<div class="section">
    <h3 class="page-control" style="margin-top: 0">Schedule Result Choice</h3>
    <div class="page-control">                    	
        <?php
            $prevPage=max(1, $curPage-1);
            echo <<< EOD
<a href="?schedule=$prevPage" class="page-no page-turn">&lt;</a>   
EOD;

            for ($i=1; $i<=$scheduleResultNum; $i++)
            {
                $selectedStr='';
                if ($curPage == $i) $selectedStr='page-selected';
                echo <<< EOD
<a href="?schedule=$i" class="page-no $selectedStr">$i</a>
EOD;
            }

            $nextPage=min($scheduleResultNum, $curPage+1);
            echo <<< EOD
<a href="?schedule=$nextPage" class="page-no page-turn">&gt;</a>
EOD;
        ?>
    </div>
</div>    
<?php
    $teacherList=PageConstant::formatOptionInSelect(ListGenerator::getTeacherName($_SESSION['scheduleDate']), $_POST['accname']);
    $isAdmin=true;

    $timetable=TimetableDB::getReliefTimetable('', '', $_SESSION['scheduleDate'], $curScheduleIndex);
    PageConstant::escapeHTMLEntity($timetable);

    $timetableIndividual=TimetableDB::getIndividualTimetable($_SESSION['scheduleDate'], $_POST['accname'], $curScheduleIndex);
    PageConstant::escapeHTMLEntity($timetableIndividual);

    include '../../timetable/relief-timetable-frag.php';
?>
<div style="clear: both"></div>
<div class="bt-control">
    <a href="index.php?result=<?php echo $_GET['schedule']; ?>" class="button">Go Back</a>
</div>
<div style="clear: both"></div>
<div id="teacher-detail"></div>
<?php
Template::printFooter();
?>