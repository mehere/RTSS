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
    public $subject;
    public $startTimeSlot;
    public $endTimeSlot;

    function __construct($aLesson,$startTimeIndex) {

        /* @var $aLesson Lesson */
        $this->subject = $aLesson->subject;
        $this->lessonId = $aLesson->lessonId;
        $this->startTimeSlot = $startTimeIndex;
        $this->endTimeSlot = $startTimeIndex + 1;
    }

    function incrementEndTime(){
        $this->endTimeSlot++;
    }
}

?>
