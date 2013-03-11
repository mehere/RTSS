<?php

spl_autoload_register(function($class){
    require_once "$class.php";
});

class BackgroundRunner
{
    public static function execInBackground($scriptPath,$argumentType, $argumentValue)
    {
        $title = escapeshellarg(basename($scriptPath));
        $phpExec = escapeshellarg(Constant::php_exe);
        $scriptPath = escapeshellarg($scriptPath);
//        $cmd = "start \"bla\" $phpExec \"$scriptPath\"";
        $cmd = "start /B $title $phpExec $scriptPath";
        for ($i=0; $i<count($argumentType); $i++){
            $cmd.= " -{$argumentType[$i]}='{$argumentValue[$i]}'";
        }
//        error_log($cmd);

        pclose(popen($cmd, 'w'));
//        error_log("end");
    }
}

?>
