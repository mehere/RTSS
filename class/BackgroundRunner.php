<?php

class BackgroundRunner
{

    const PHP_EXE = 'C:\Program Files (x86)\PHP\v5.3\php.exe';

    static function execInBackground($scriptPath)
    {
        $phpExec = '"' . self::PHP_EXE . '"';
        pclose(popen("start \"bla\" $phpExec \"$scriptPath\"&", 'w'));
    }
}

?>
