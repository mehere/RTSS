<?php
spl_autoload_register(function($class){
    require_once "class/$class.php";
});

Template::validate(true, false);

session_destroy();

header("Location: /RTSS2/");
?>
