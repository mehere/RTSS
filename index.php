<?php 
spl_autoload_register(function($class){
    require_once "class/$class.php";
});

session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?php echo PageConstant::SCH_NAME_ABBR . " " . PageConstant::PRODUCT_NAME; ?></title>
<link href="/RTSS/css/main.css" rel="stylesheet" type="text/css" />
<link href="/RTSS/css/index.css" rel="stylesheet" type="text/css" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js" type="text/javascript"></script>
<script src="/RTSS/js/index.js"></script>
</head>
<body>
<div id="container">
    <div id="content-wrapper">
    	<div id="content">
        	<img src="/RTSS/img/school-logo-name.png" alt="<?php echo PageConstant::SCH_NAME ?>" class="logo-name" />
            <form method="post" action="_login.php" class="login-form" name="login">
                <div>Log in to <?php echo PageConstant::PRODUCT_NAME ?></div>
                <input type="text" name="username" value="User Name" class="textfield" />
                <input type="password" name="password" value="Password" class="textfield" />
                <input type="submit" value="Login" class="button button-small" />
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
unset($_SESSION['loginError']);
?>