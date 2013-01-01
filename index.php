<?php 
    include_once 'head-frag.php';
?>
<title><?php echo Constant::SCH_NAME_ABBR . " " . Constant::PRODUCT_NAME; ?></title>
<link href="/RTSS/css/main.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/index.css" rel="stylesheet" type="text/css" />
<script src="/RTSS/js/index.js"></script>
</head>
<body>

<div id="container">  	
    <div id="content-wrapper">
    	<div id="content">
        	<img src="/RTSS/img/school-logo-name.png" alt="<?php echo Constant::SCH_NAME ?>" class="logo-name" />
            <form method="post" action="/RTSS/relief/" class="login-form">
                <div>Log in to <?php echo Constant::PRODUCT_NAME ?></div>
                <input type="text" name="username" value="User Name" class="textfield" />
                <input type="password" name="password" value="Password" class="textfield" />
                <input type="submit" value="Login" class="button" />
                <!--a href="">Forgot password?</a-->
            </form>
            <div class="comment-bottom">Copyright @ <?php echo date("Y") . "&nbsp;&nbsp;" . Constant::SCH_NAME; ?><span style="margin-left: 1.5em; font-size: .8em">All rights reserved</span></div>
        </div>
    </div>
</div>
    
</body>
</html>