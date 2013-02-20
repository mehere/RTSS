<?php

class SkippedLesson
{
    public $teacherId;
    public $startTimeIndex;

    public function __construct($teacherId, $startTimeIndex)
    {
        $this->teacherId = $teacherId;
        $this->startTimeIndex = $startTimeIndex;
    }
}

?>
