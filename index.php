<?php 
include_once 'php-head.php';
include_once 'head-frag.php';
?>
<title><?php echo PageConstant::SCH_NAME_ABBR . " " . PageConstant::PRODUCT_NAME; ?></title>
<link href="/RTSS/css/main.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/index.css" rel="stylesheet" type="text/css" />
<script src="/RTSS/js/index.js"></script>
</head>
<body>
<div id="container">  	
    <div id="content-wrapper">
    	<div id="content">
        	<img src="/RTSS/img/school-logo-name.png" alt="<?php echo PageConstant::SCH_NAME ?>" class="logo-name" />
            <form method="post" action="_login.php" class="login-form">
                <div>Log in to <?php echo PageConstant::PRODUCT_NAME ?></div>
                <input type="text" name="username" value="User Name" class="textfield" />
                <input type="password" name="password" value="Password" class="textfield" />
                <input type="submit" value="Login" class="button" />
                <!--a href="">Forgot password?</a-->
                <div class="error-msg"><?php if ($_SESSION['loginError']) echo PageConstant::$ERROR_TEXT['login']['mismatch']; ?></div>
            </form>
            <div class="comment-bottom">Copyright @ <?php echo date("Y") . "&nbsp;&nbsp;" . PageConstant::SCH_NAME; ?><div style="margin-top: 5px; font-size: .85em">All rights reserved</div></div>            
        </div>
    </div>
</div>
</body>
</html>
<?php 
$_SESSION['loginError']='';
?>