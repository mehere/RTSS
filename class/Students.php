<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Students
 *
 * @author Wee
 */
class Students {

    //put your code here
    public $name; //class name
    public $timetable;

    public function __construct($name) {
        $this->name = $name;
        $this->timetable = array();
    }

}

?>
