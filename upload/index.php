<?php
spl_autoload_register(function($class){
    require_once "../class/$class.php";
});

Template::printHeaderAndDoValidation('Upload Timetable', 
        array('upload.css'), 
        array(''), 
        Template::TT_ADMIN, 'Master Timetable', Template::TT_ADMIN_MASTER);
?>
<form class="main" name="timetable" action="_upload.php" method="post" enctype="multipart/form-data">
    <div class="line"><span class="label">Year:</span>
        <select name="year">
            <?php echo PageConstant::printYearRange(); ?>
        </select>
    </div>
    <div class="line"><span class="label">Semester:</span>
        <select name="sem">
            <?php echo PageConstant::printSemRange(); ?>
        </select>
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
    <div class="line"><span class="label">&nbsp;</span><input type="submit" value="Upload" name="submit" style="font-size: .9em" class="button" /></div>
</form>
<div id="dialog-alert"><?php echo $_SESSION['uploadSuccess']; ?></div>
<?php
    Template::printFooter();

    unset($_SESSION['uploadError']);
    unset($_SESSION['uploadSuccess']);
?>
