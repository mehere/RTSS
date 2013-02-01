<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ReliefLesson
 *
 * @author Wee
 */
class ReliefLesson
{
    //put your code here

    public $lessonId;
    public $startTimeSlot;
    public $endTimeSlot;

    function __construct($lessonId,$startTimeIndex) {

        /* @var $aLesson Lesson */
        $this->lessonId = $lessonId;
        $this->startTimeSlot = $startTimeIndex;
        $this->endTimeSlot = $startTimeIndex + 1;
    }

    function incrementEndTime(){
        $this->endTimeSlot++;
    }
}

?>
