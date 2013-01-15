<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
        <?php
        // put your code here
        function __autoload($class) {
            $paths = explode(PATH_SEPARATOR, get_include_path());

            $flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE;
            $file = strtolower(str_replace("\\", DIRECTORY_SEPARATOR, trim($class, "\\"))) . ".php";

            foreach ($paths as $path) {
                $combined = $path . DIRECTORY_SEPARATOR . $file;

                if (file_exists($combined)) {
                    require_once($combined);
                    return;
                }
            }

            throw new Exception("{$class} not found");
        }

        //spl_autoload_register('autoload');

        $analyzer = new TimetableAnalyzer();
        $analyzer->readCsv();
        
        ?>
    </body>
</html>
