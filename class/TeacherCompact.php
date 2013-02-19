<?php

class TeacherCompact
{

    public static $arrTeachers;
    public static $arrLesson;
    public static $typeToGroupMap;
    public static $typeToNeedReliefMap;

    const AVAILABILITY_FREE = 1;
    const AVAILABILITY_SKIPPED = 2;
    const AVAILABILITY_PARTIAL = 3;
    const AVAILABILITY_BUSY = 4;
    const TYPE_LEAVE = 0;
    const TYPE_LESSON = 1;
    const TYPE_OPTIONAL = 2;
    const TYPE_OPTIONAL_REPLACED = 3;
    const TYPE_OPTIONAL_CANCELLED = 4;
    const MAX_LESSONS = 14;

    public static $recommendedNoOfLessons = self::MAX_LESSONS;
    public $timetable;
    public $teacherId;
    public $noTeachingPeriod;
    public $noMandatoryPeriods;
    public $netRelived;
    public $hasDone;

    public function __construct($teacherId, $type)
    {
        /* @var $fullTeacher Teacher */
        $this->teacherId = $teacherId;
        $fullTeacher = self::$arrTeachers[$teacherId];
        $fullTeacher->type = $type;
        $fullTeacher->typeGroup = self::$typeToGroupMap[$type];
        $this->timetable = array();
        $this->noTeachingPeriod = 0;
        $this->noMandatoryPeriods = 0;
        $this->hasDone = FALSE;

        if ($type == "Temp")
        {
            $this->constructTempTeacher($fullTeacher);
        } else
        {
            $this->constructTeacher($fullTeacher);
        }
    }

    private function constructTempTeacher($fullTeacher)
    {
        /* @var $fullTeacher Teacher */
        $this->netRelived
                = $fullTeacher->noLessonRelived;
        $availability = $fullTeacher->availability;
        $freeSlots = array();
        foreach ($availability as $availableSlot)
        {
            $startIndex = $availableSlot[0];
            $endIndex = $availableSlot[1];
            for ($i = $startIndex; $i < $endIndex; $i++)
            {
                $freeSlots[$i] = NULL;
            }
        }
        for ($i = 1; $i <= self::MAX_LESSONS; $i++)
        {
            if (!isset($freeSlots))
            {
                $this->timetable[$i] = TeacherCompact::TYPE_LEAVE;
            }
        }
    }

    private function constructTeacher($fullTeacher)
    {
        /* @var $fullTeacher Teacher */
        /* @var $aLesson Lesson */
        $this->netRelived
                = $fullTeacher->noLessonRelived - $fullTeacher->noLessonMissed;
        $hisTimetable = $fullTeacher->timetable;

        foreach ($hisTimetable as $timeIndex => $aLesson)
        {
            if ($aLesson->isHighlighted)
            {
                $this->timetable[$timeIndex] = self::TYPE_LESSON;
                $this->noTeachingPeriod++;
                $this->noMandatoryPeriods++;
            } else
            {
                $this->timetable[$timeIndex] = self::TYPE_OPTIONAL;
                $this->noTeachingPeriod++;
            }
        }
    }

    public function onLeave($leaveRecords)
    {
        /* @var $fullTeacher Teacher */
        /* @var $aLesson Lesson */
        $fullTeacher = self::$arrTeachers[$this->teacherId];
        if (self::$typeToNeedReliefMap[$fullTeacher->type])
        {
            $lessonsNeedRelief = array();
            foreach ($leaveRecords as $aLeave)
            {
                $startLeaveIndex = $aLeave["startLeave"];
                $endLeaveIndex = $aLeave["endLeave"];

                $lastLesson = null;
                $aReliefLesson = null;
                for ($i = $startLeaveIndex; $i < $endLeaveIndex; $i++)
                {
//                    $this->noBusyPeriods++;
                    if ((isset($fullTeacher->timetable[$i])) &&
                            (!empty($fullTeacher->timetable[$i]->classes)))
                    {
                        $aLesson = $fullTeacher->timetable[$i];

                        if ($aLesson !== $lastLesson)
                        {
                            self::$arrLesson[$aLesson->lessonId] = $aLesson;
                            $aReliefLesson = new ReliefLesson($this->teacherId, $aLesson->lessonId, $i);
                            $lessonsNeedRelief[$aReliefLesson->toString()] = $aReliefLesson;
                        } else
                        {
                            $aReliefLesson->incrementEndTime();
                        }
                        $this->noTeachingPeriod--;
                    } else
                    {
                        $aLesson = null;
                        $aReliefLesson = null;
                    }

                    $this->timetable[$i] = self::TYPE_LEAVE;
                    $lastLesson = $aLesson;
                }
            }


            return $lessonsNeedRelief;
        }
    }

    public function isAvailable()
    {
        return ($this->noMandatoryPeriods < self::$recommendedNoOfLessons);
    }

    /**
     *
     * @param type $timeIndex
     * @return boolean: hasToSkipLesson
     */
    public function setLesson($timeIndex)
    {
        $this->noMandatoryPeriods++;
        if (isset($this->timetable[$timeIndex]))
        {
            $aLesson = $this->timetable[$timeIndex];
            if ($aLesson == TeacherCompact::TYPE_OPTIONAL)
            {
                $this->timetable[$timeIndex] = TeacherCompact::TYPE_OPTIONAL_REPLACED;
                return TRUE;
            } else //if ($aLesson == TeacherCompact::TYPE_OPTIONAL_CANCELLED
            {
                $this->timetable[$timeIndex] = TeacherCompact::TYPE_OPTIONAL_REPLACED;
                $this->netRelived++;
                $this->noTeachingPeriod++;
                return FALSE;
            }
        } else
        {
            $this->timetable[$timeIndex] = TeacherCompact::TYPE_LESSON;
            $this->noTeachingPeriod++;
            $this->netRelived++;
            return FALSE;
        }
    }

    public function cancelExcess()
    {
        if ($this->noTeachingPeriod > self::$recommendedNoOfLessons)
        {
            $noCancelled = 0;
            foreach ($this->timetable as $key => $aLesson)
            {
                if ($aLesson == self::TYPE_OPTIONAL)
                {
                    $this->timetable[$key] = self::TYPE_OPTIONAL_CANCELLED;
                    $this->noTeachingPeriod--;
                    $this->netRelived--;
                    $noCancelled++;

                    if ($this->noTeachingPeriod == self::$recommendedNoOfLessons)
                    {
                        break;
                    }
                }
            }
            return $noCancelled;
        } else
        {
            return 0;
        }
    }

    public function canTeach($aLesson)
    {
        /* @var $aLesson ReliefLesson */
        $noMatch = 0;
        $timetable = $this->timetable;
        $hasOptional = FALSE;
        $numberOfLessons = $aLesson->endTimeSlot - $aLesson->startTimeSlot;
        $gotTime = (($this->noMandatoryPeriods + $numberOfLessons) <= self::$recommendedNoOfLessons);

        for ($i = $aLesson->startTimeSlot; $i < $aLesson->endTimeSlot; $i++)
        {
            if (!isset($timetable[$i]))
            {
                $noMatch++;
            } else if ($timetable[$i] == self::TYPE_OPTIONAL)
            {
                $noMatch++;
                $hasOptional = TRUE;
            }
        }

        if ($gotTime && ($numberOfLessons == $noMatch))
        {
            if ($hasOptional)
            {

                return self::AVAILABILITY_SKIPPED;
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

    public function getTypeNo()
    {
        return self::$arrTeachers[$this->teacherId]->typeGroup;
    }

    static function init()
    {
        self::$typeToGroupMap = array("Temp" => 1, "Aed" => 1, "Untrained" => 1, "Normal" => 2, "Hod" => 3);
        self::$typeToNeedReliefMap = array("Temp" => FALSE, "Aed" => FALSE, "Untrained" => FALSE, "Normal" => TRUE, "Hod" => TRUE);
    }

    static function getAccName($teacherId){
        /* @var $aTeacher Teacher*/
        $aTeacher = self::$arrTeachers[$teacherId];
        return $aTeacher->accname;
    }

}

TeacherCompact::init();
?>
