<?php

class BackgroundRunner
{

    const PHP_EXE = 'C:\Program Files (x86)\PHP\v5.3\php.exe';

    static function execInBackground($scriptPath,$argumentType, $argumentValue)
    {
        $phpExec = '"' . self::PHP_EXE . '"';
        $cmd = "start \"bla\" $phpExec \"$scriptPath\" ";
        for ($i=0; $i<count($argumentType); $i++){
            $cmd.= "-{$argumentType[$i]}='{$argumentValue[$i]}' ";
        }
        $cmd .= "&";
        error_log($cmd);
        pclose(popen($cmd, 'w'));
    }
}

?>
