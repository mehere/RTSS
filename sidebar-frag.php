<div id="sidebar">
    <img src="/RTSS/img/school-logo.png" alt="CHIJ" class="logo" />
    <h1><?php echo PageConstant::PRODUCT_NAME; ?></h1>
    <ul class="nav">
        <?php if ($_SESSION['type'] == 'admin') { ?>
        <li><a href="/RTSS/relief/">Scheduling</a></li>
        <li><a href="/RTSS/report/">Report</a></li>
        <li><a href="/RTSS/timetable/upload.php">Timetable - Admin</a></li>
        <?php } ?>
        <li><a href="/RTSS/timetable/">View Timetable</a></li>
    </ul>
</div>
<div id="footer">
    <div style="position: absolute; left: 0; bottom: 5px; width: 100%">
        Copyright @ <?php echo date("Y"); ?>
        <div style="font-size: .9em"><?php echo PageConstant::SCH_NAME ?></div>
        <p style="margin-top: .3em; font-size: .8em">All rights reserved</p>
    </div>
</div>
