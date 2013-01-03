<?php 
    include_once '../../head-frag.php';
?>
<title><?php echo Constant::SCH_NAME_ABBR . " " . Constant::PRODUCT_NAME; ?></title>
<link href="/RTSS/css/main.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/schedule.css" rel="stylesheet" type="text/css" />
<script src="/RTSS/js/timetable.js"></script>

<link href="/RTSS/jquery-ui/ui-lightness/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css" />
<script src="/RTSS/jquery-ui/jquery-ui-1.9.2.custom.min.js"></script>

</head>
<body>

<div id="container">  	
    <div id="content-wrapper">
    	<div id="content">
            <div id="topbar">
            	<div class="fltrt">XXX | <a href="/RTSS/">Log out</a></div>
                <ul class="breadcrumb">
                    <li><a href="/RTSS/relief/">Scheduling</a></li>
                    <li>Result Preview</li>
                </ul>                
            </div>
            <div id="tabs">
                <ul>
                    <li><a href="#tabs-1">Class</a></li>
                    <li><a href="/RTSS/timetable/_timetable.php?tab=teacher&mode=preview">Teacher</a></li>
                </ul>
                <div id="tabs-1">
                    <p>Proin elit arcu, rutrum commodo, vehicula tempus, commodo a, risus. Curabitur nec arcu. Donec sollicitudin mi sit amet mauris. Nam elementum quam ullamcorper ante. Etiam aliquet massa et lorem. Mauris dapibus lacus auctor risus. Aenean tempor ullamcorper leo. Vivamus sed magna quis ligula eleifend adipiscing. Duis orci. Aliquam sodales tortor vitae ipsum. Aliquam nulla. Duis aliquam molestie erat. Ut et mauris vel pede varius sollicitudin. Sed ut dolor nec orci tincidunt interdum. Phasellus ipsum. Nunc tristique tempus lectus.</p>
                </div>
            </div>
            <form class="result" method="get" action="result.php">
                <fieldset>
                    <legend>Schedule 1</legend>
                    <table class="result-table">
                    	<tr><td>Type:</td><td>Relief</td></tr>
                        <tr><td>Min Period:</td><td>2</td></tr>
                        <tr><td>Subject:</td><td>Almost</td></tr>
                    </table>
                    <input type="submit" value="View" class="button" />
                </fieldset>
                <input type="hidden" name="scheduleIndex" value="0" />                
            </form>
            <form class="result">
                <fieldset>
                    <legend>Schedule 2</legend>
                    <table class="result-table">
                    	<tr><td>Type:</td><td>AED</td></tr>
                        <tr><td>Min Period:</td><td>3</td></tr>
                        <tr><td>Subject:</td><td>Almost</td></tr>
                    </table>
                </fieldset>
            </form>
        </div>        
    </div>
    <?php include '../../sidebar-frag.php'; ?>
</div>
    
</body>
</html>