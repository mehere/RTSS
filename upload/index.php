<?php
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

Template::printHeaderAndDoValidation('Upload Timetable', 
        array('upload.css'), 
        array('upload-timetable.js'), 
        Template::TT_ADMIN, 'Master Timetable', Template::TT_ADMIN_MASTER);

$semDates=SchoolTime::getSemPeriod($_POST['year'], $_POST['sem']);
?>
<form class="main" name="timetable" action="_upload.php" method="post" enctype="multipart/form-data">
    <div class="line">
        <span class="label">Year:</span>
        <select name="year" style="float: left;">
            <?php echo PageConstant::printYearRange($_POST['year']); ?>
        </select>
        <span class="label">Semester:</span>
        <select name="sem">
            <?php echo PageConstant::printSemRange($_POST['sem']); ?>
        </select>
    </div>
    <div class="line">
        <span class="label">Sem Start:</span>
        <input type="text" class="textfield datefield" name="sem-date-start" maxlength="10" /><input type="hidden" name="server-sem-date-start" value="<?php echo $semDates[0]; ?>" /> <img class="calendar-trigger" src="/RTSS2/img/calendar.gif" alt="Calendar" style="vertical-align: middle; cursor: pointer" />        
    </div>
    <div class="line">
        <span class="label">Sem End:</span>
        <input type="text" class="textfield datefield" name="sem-date-end" maxlength="10" /><input type="hidden" name="server-sem-date-end" value="<?php echo $semDates[1]; ?>" /> <img class="calendar-trigger" src="/RTSS2/img/calendar.gif" alt="Calendar" style="vertical-align: middle; cursor: pointer" />
    </div>
    <div class="line"><span class="label">File:</span><input type="file" name="timetableFile" /></div>
    <?php
        $msg = $_SESSION['uploadError'];
        if ($msg)
        {                    
            echo <<< EOD
<div class="error-msg" style="margin-bottom: -10px">$msg</div>   
EOD;
        }        
    ?>
    <div class="line">
        <span class="label">&nbsp;</span>
        <input type="submit" value="Upload" class="button button-small" />
    </div>
</form>
<div id="dialog-confirm"></div>
<div id="dialog-alert"><?php echo $_SESSION['uploadSuccess']; ?></div>
<?php
    Template::printFooter();

    unset($_SESSION['uploadError']);
    unset($_SESSION['uploadSuccess']);
?>
