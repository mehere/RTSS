<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

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
        return "Error Code : ".$this->code." <br>".$this->message."<br>Error at file <b>".$this->file_localtion."</b> line <b>".$this->line_location."</b><br>";
    }
}
?>
