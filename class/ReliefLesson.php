<?php
class ReliefLesson
{

    public $lessonId;
    public $teacherOriginal;
    public $teacherRelief;
    public $startTimeSlot;
    public $endTimeSlot;

    function __construct($aTeacherAccname, $aLessonId, $startTimeIndex)
    {

        /* @var $aLesson Lesson */
        $this->teacherOriginal = $aTeacherAccname;
        $this->teacherRelief = NULL;

        $this->lessonId = $aLessonId;
        $this->startTimeSlot = $startTimeIndex;
        $this->endTimeSlot = $startTimeIndex + 1;
    }

    function incrementEndTime()
    {
        $this->endTimeSlot++;
    }

    function toString()
    {
        if (empty($this->teacherRelief))
        {
            $lessonString = "{$this->lessonId}[{$this->startTimeSlot},{$this->endTimeSlot}]({$this->teacherOriginal})";
        } else
        {
            $lessonString = "{$this->lessonId}[{$this->startTimeSlot},{$this->endTimeSlot}]({$this->teacherOriginal})-{$this->teacherRelief}";
        }
        return $lessonString;
    }
}

?>
