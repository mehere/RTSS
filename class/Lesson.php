<?php
spl_autoload_register(function($class){
    require_once "$class.php";
});

class Lesson {

    public $lessonId;
    public $teachers;
    public $classes;
    public $venue;
    public $subject;
    public $day;
    public $startTimeSlot;
    public $endTimeSlot;
    public $isMandatory;

    function __construct(DayTime $dayTime, $subject, $venue) {

        $this->teachers = array();
        $this->classes = array();
        $this->subject = $subject;
        $this->venue = $venue;

        $this->day = $dayTime->dayIndex;
        $this->startTimeSlot = $dayTime->timeIndex;
        $this->endTimeSlot = $this->startTimeSlot + 1;

        $this->isMandatory = TRUE;
    }

    function addClass(Students $aClass){
        $this->classes[$aClass->name] = $aClass;
    }

    function addTeacher(Teacher $aTeacher){
        $this->teachers[$aTeacher->abbreviation] = $aTeacher;
    }

    function incrementEndTime(){
        $this->endTimeSlot++;
    }
}

?>
