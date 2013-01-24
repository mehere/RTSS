<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DayTime
 *
 * @author Wee
 */
class DayTime {

    //put your code here
    public $dayIndex;
    public $timeIndex;

    public function __construct($dayIndex, $timeIndex) {
        $this->dayIndex = $dayIndex;
        $this->timeIndex = $timeIndex;
    }

    //check whether $another is the next time slot of this
    function isNext(DayTime $another) {
        if (!($this->dayIndex == $another->dayIndex)) {
            return FALSE;
        }
        if (!(($this->timeIndex + 1) == $another->timeIndex)) {
            return FALSE;
        }
        return true;
    }

    
}

?>
