<?php 
    include_once '../head-frag.php';
?>
<title><?php echo Constant::SCH_NAME_ABBR . " " . Constant::PRODUCT_NAME; ?></title>
<link href="/RTSS/css/main.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/relief.css" rel="stylesheet" type="text/css" />
<script src="/RTSS/js/relief.js"></script>

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
                    <li><a href="">Scheduling</a></li>
                    <li><a href="">Report</a></li>
                    <li>Here</li>
                </ul>                
            </div>
            <form class="main" name="schedule" action="" method="post">
            	Date: <input type="text" class="textfield" name="date" /> XXX
                <div class="section">
                	Teacher on Leave: <a href="">Edit/Add</a>
                    <table class="table-info">
                        <thead>
                            <tr>
                                <th style="width: 20%"><span class="sort" search="username">Name</span></th>
                                <th style="width: 80px"><span class="sort" search="email">Type</span></th>
                                <th style="width: 40%"><span class="sort" search="occupation">Reason</span></th>
                                <th style="width: 80px"><span class="sort" search="residence">Verified</span></th>
                                <th style="width: 100px"><span class="sort" search="login_time">Scheduled</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        	<tr><td>haha asdf</td><td>AED</td><td>haha</td><td>Yes</td><td>No</td></tr>
                            <tr><td>haha asdf</td><td>Normal</td><td>haha</td><td>Yes</td><td>No</td></tr>
                            <tr><td>hahaasdfasdfasadsfasdfadsfasdf dsfg</td><td>haha d</td><td>haha asdf asd fasd fasd fasd fasd f</td><td>Yes</td><td>No</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="section">
                	Temporary Relief Teacher: <a href="">Edit/Add</a>
                    <table class="table-info">
                        <thead>
                            <tr>
                                <th style="width: 20%"><span class="sort" search="username">Name</span></th>
                                <th style="width: 80px"><span class="sort" search="email">Type</span></th>
                                <th style="width: 40%"><span class="sort" search="occupation">Reason</span></th>
                                <th style="width: 80px"><span class="sort" search="residence">Verified</span></th>
                                <th style="width: 100px"><span class="sort" search="login_time">Scheduled</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        	<tr><td>haha asdf</td><td>AED</td><td>haha</td><td>Yes</td><td>No</td></tr>
                            <tr><td>haha asdf</td><td>Normal</td><td>haha</td><td>Yes</td><td>No</td></tr>
                            <tr><td>haha asdf</td><td>haha d</td><td>haha</td><td>Yes</td><td>No</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="bt-control">
                	<input type="submit" value="Schedule All" class="button" />
                    <input type="submit" value="Adhoc Schedule" class="button" />
                </div>                
            </form>
        </div>
    </div>
    <?php include_once '../sidebar-frag.php'; ?>
</div>
    
</body>
</html>