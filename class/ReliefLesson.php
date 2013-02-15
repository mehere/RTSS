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

    const AVAILABILITY_FREE = "Free";
    const AVAILABILITY_PARTIAL = "Partial";
    const AVAILABILITY_BUSY = "Busy";
    const AVAILABILITY_SKIPPED = "Skipped";

//put your code here

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

    public function canBeDoneBy($aTeacher)
    {
        /* @var $aTeacher TeacherCompact */
        $noMatch = 0;
        $timetable = $aTeacher->timetable;
        $hasOptional = FALSE;
        for ($i = $this->startTimeSlot; $i < $this->endTimeSlot; $i++)
        {
            if (!isset($timetable[$i]))
            {
                $noMatch++;
            } else if ($timetable[$i] == TeacherCompact::TYPE_OPTIONAL)
            {
                $noMatch++;
                $hasOptional = TRUE;
            }
        }
        $numberOfLessons = $this->endTimeSlot - $this->startTimeSlot;
        if ($numberOfLessons == $noMatch)
        {
            if ($hasOptional)
            {
                return self::AVAILABIITY_SKIPPED;
            } else
            {
                return self::AVAILABILITY_FREE;
            }
        } else if ($noMatch > 0)
        {
            return self::AVAILABILITY_PARTIAL;
        }
        else
            return self::AVAILABILITY_BUSY;
    }

}

?>
