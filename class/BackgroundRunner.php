<?php

spl_autoload_register(function($class){
    require_once "$class.php";
});

class BackgroundRunner
{

    const PHP_EXE = Constant::php_exe;

    public static function execInBackground($scriptPath,$argumentType, $argumentValue)
    {
        $phpExec = escapeshellarg(Constant::php_exe);
        $scriptPath = escapeshellarg($scriptPath);
//        $cmd = "start \"bla\" $phpExec \"$scriptPath\"";
        $cmd = "start /B \"bg\" $phpExec $scriptPath";
        for ($i=0; $i<count($argumentType); $i++){
            $cmd.= " -{$argumentType[$i]}='{$argumentValue[$i]}'";
        }
//        $cmd .= "&";
        error_log($cmd);
        pclose(popen($cmd, 'w'));
    }
}

?>
