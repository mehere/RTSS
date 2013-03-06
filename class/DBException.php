<?php

class DBException extends Exception
{
    public $file_localtion;
    public $line_location;
    
    public function __construct($message, $file, $line, $code=0) {
        parent::__construct($message, $code);
        $this->file_localtion = basename($file);
        $this->line_location = $line;
    }
    
    public function __toString() {
        return "Error Code : ".$this->code.". ".$this->message.". Error at file ".$this->file_localtion." line ".$this->line_location."";
    }
}
?>
