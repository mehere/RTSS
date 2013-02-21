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

    public function toString()
    {
        $startTime = sprintf("%02d", $this->startTimeIndex);
        $results = "$this->teacherId($startTime)";
        return $results;
    }
}

?>
