<?php
spl_autoload_register(function($class){
    require_once "../../class/$class.php";
});

Template::validate(true);

for ($i=0; $i<$_POST['num']; $i++)
{
    if ($_POST["unavailable-$i"])
    {
//        var_dump($i, $_POST["reliefID-$i"], SchoolTime::getTimeValue($_POST["busy-from-$i"]+1), SchoolTime::getTimeValue($_POST["busy-to-$i"]+1));
        AdHocSchedulerDB::cancelRelief($_POST["reliefID-$i"], $_POST["busy-from-$i"]+1, $_POST["busy-to-$i"]+1);
    }
}

header("Location: _schedule.php");
?>