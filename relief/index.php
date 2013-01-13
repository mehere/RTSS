<?php 
    include_once '../head-frag.php';
?>
<title><?php echo Constant::SCH_NAME_ABBR . " " . Constant::PRODUCT_NAME; ?></title>
<link href="/RTSS/css/main.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/relief.css" rel="stylesheet" type="text/css" />
<script src="/RTSS/js/relief.js"></script>
<script src="/RTSS/js/teacher-detail.js"></script>

<link href="/RTSS/jquery-ui/ui-lightness/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css" />
<script src="/RTSS/jquery-ui/jquery-ui-1.9.2.custom.min.js"></script>

<style type="text/css">
#align-teacher tr td:nth-child(1), #align-teacher tr td:nth-child(3) {
	text-align: left;
	word-wrap: break-word;
}
#align-temp tr td:nth-child(1), #align-temp tr td:nth-child(4) {
	text-align: left;
	word-wrap: break-word;
}
</style>
</head>
<body>

<div id="container">  	
    <div id="content-wrapper">
    	<div id="content">
            <div id="topbar">
            	<div class="fltrt">XXX | <a href="/RTSS/">Log out</a></div>
                <ul class="breadcrumb">
                    <li><a href="/RTSS/relief/">Scheduling</a></li>
                    <li>Start</li>
                </ul>                
            </div>
            <form class="main" name="schedule" action="schedule/" method="post">
            	Date: <input type="text" class="textfield" name="date" maxlength="10" /> <img id="calendar-trigger" src="/RTSS/img/calendar.png" alt="Calendar" style="vertical-align: middle; cursor: pointer" width="36" />
                <div class="section">
                	Teacher on Leave: <a href="teacher-edit.php">Edit/Add</a>
                    <table class="table-info">
                        <thead>
                            <tr>
                                <th style="width: 30%" class="sort">Name<span class="ui-icon ui-icon-arrowthick-2-n-s"></span></th>
                                <th style="width: 80px" class="sort"><span class="sort" search="email">Type</span></th>
                                <th style="width: 70%" class="sort"><span class="sort" search="occupation">Reason</span></th>
                                <th style="width: 80px" class="sort"><span class="sort" search="residence">Verified</span></th>
                                <th style="width: 100px" class="sort"><span class="sort" search="login_time">Scheduled</span></th>
                            </tr>
                        </thead>
                        <tbody id="align-teacher">
                            <tr><td><a href="_teacher_detail.php?accname=ycw">James Yeo Chuan Wee</a></td><td>Big Boss</td><td>Sun burn</td><td>Yes</td><td>No</td></tr>
                            <tr><td><a href="_teacher_detail.php?accname=cr">Chelliah Rama</a></td><td>Smaller Boss</td><td>Sun burn</td><td>Yes</td><td>No</td></tr>
                            <tr><td><a href="_teacher_detail.php?accname=ps">Prashanth</a></td><td>Little Boss</td><td>Sun burn</td><td>Yes</td><td>No</td></tr>                            
                        </tbody>
                    </table>
                </div>
                <div class="section">
                	Temporary Relief Teacher: <a href="teacher-edit.php?teacher=temp">Edit/Add</a>
                    <table class="table-info">
                        <thead>
                            <tr>
                                <th style="width: 30%" class="sort"><span class="sort" search="username">Name</span></th>
                                <th style="width: 110px" class="sort"><span class="sort" search="email">Handphone</span></th>
                                <th style="width: 140px" class="sort"><span class="sort" search="occupation">Time Avaialble</span></th>
                                <th style="width: 70%" class="sort"><span class="sort" search="residence">Remark</span></th>                                
                            </tr>
                        </thead>
                        <tbody id="align-temp">
                        	<tr><td>haha asdf</td><td>09234543</td><td>0900-1500</td><td>asdf asdf </td></tr>
                            <tr><td>haha asdf</td><td>09234543</td><td>0900-1500</td><td>asdf asdf </td></tr>
                            <tr><td>haha asdf</td><td>09234543</td><td>0900-1500</td><td>asdf asdf </td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="bt-control">
                	<input type="submit" value="Schedule All" class="button" />
                    <input type="submit" value="Adhoc Schedule" class="button" />
                </div>                
            </form>
            <div id="teacher-detail">Loading ...</div>
        </div>        
    </div>
    <?php include '../sidebar-frag.php'; ?>
</div>
    
</body>
</html>