<?php
spl_autoload_register(function($class)
        {
            include "./class/$class.php";
        });
?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
        <meta http-equiv="Pragma" content="no-cache" />
        <meta http-equiv="Expires" content="0" />
        <title></title>
    </head>
    <body>
        <?php
        // put your code here

//        echo "123";
        BackgroundRunner::execInBackground("C:\Users\Wee\Documents\NetBeansProjects\RTSS\TestScript.php");
        echo "Hellloooo";

//        BackgroundRunner::execInBackground("php ./TestScript.php");
        ?>
    </body>
</html>
