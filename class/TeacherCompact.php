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
                $freeSlots[$i] = TRUE;
            }
        }
        for ($i = 1; $i <= self::MAX_LESSONS; $i++)
        {
            if (!isset($freeSlots[$i]))
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
            if ($aLesson->isMandatory)
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

    public function onLeave($leaveRecords, &$excludingTeachers)
    {
        /* @var $fullTeacher Teacher */
        /* @var $aLesson Lesson */
        $fullTeacher = self::$arrTeachers[$this->teacherId];

        $lessonsNeedRelief = array();


        $needRelief = self::$typeToNeedReliefMap[$fullTeacher->type];
//        echo("<br> AccName: $fullTeacher->accname");
//        echo("<br> Type: $fullTeacher->type");
//        echo("<br>Need Relief: $needRelief");

        foreach ($leaveRecords as $aLeave)
        {
            $startLeaveIndex = $aLeave["startLeave"];
            $endLeaveIndex = $aLeave["endLeave"];

            $lastLesson = null;
            $aReliefLesson = null;
//            echo "<br>Timetable";
//            print_r($fullTeacher->timetable);
            for ($i = $startLeaveIndex; $i < $endLeaveIndex; $i++)
            {
                if ((isset($fullTeacher->timetable[$i])) &&
                        (!empty($fullTeacher->timetable[$i]->classes)))
                {

                    $aLesson = $fullTeacher->timetable[$i];
                    if ($needRelief)
                    {

                        if ($aLesson !== $lastLesson)
                        {

                            self::$arrLesson[$aLesson->lessonId] = $aLesson;
                            $aReliefLesson = new ReliefLesson($this->teacherId, $aLesson->lessonId, $i);
                            $lessonsNeedRelief[$aReliefLesson->toString()] = $aReliefLesson;
                        } else
                        {
                            $aReliefLesson->incrementEndTime();
                        }
                    }

                    if ($aLesson->isMandatory)
                    {
                        $this->noTeachingPeriod--;
                        $this->noMandatoryPeriods--;
                    } else
                    {
                        $this->noTeachingPeriod--;
                    }
                } else
                {
                    $aLesson = null;
                    $aReliefLesson = null;
                }
                $lastLesson = $aLesson;
                $this->timetable[$i] = self::TYPE_LEAVE;
            }
        }


        // check if should be excluded
        $shouldExclude = TRUE;
        for ($i = 1; $i <= TeacherCompact::MAX_LESSONS; $i++)
        {
            if (!isset($this->timetable[$i]))
            {
                $shouldExclude = FALSE;
                break;
            } else if ($this->timetable[$i] == self::TYPE_OPTIONAL)
            {
                $shouldExclude = FALSE;
                break;
            }
        }
        if ($shouldExclude)
        {
//            print_r($excludingTeachers);
//            echo "<br>";
            $excludingTeachers[self::getAccName($this->teacherId)] = TRUE;
        }

        return $lessonsNeedRelief;
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

    public function cancelExcess(&$lessonsSkipped)
    {
        if ($this->noTeachingPeriod > self::$recommendedNoOfLessons)
        {
            $noCancelled = 0;
            foreach ($this->timetable as $timeIndex => $aLesson)
            {
                if ($aLesson == self::TYPE_OPTIONAL)
                {
                    $this->timetable[$timeIndex] = self::TYPE_OPTIONAL_CANCELLED;
                    $this->noTeachingPeriod--;
                    $this->netRelived--;
                    $noCancelled++;
                    $skipLesson = new SkippedLesson($this->teacherId, $timeIndex);
                    $lessonsSkipped[$skipLesson->toString()] = $skipLesson;

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
        self::$typeToNeedReliefMap = array("Temp" => FALSE, "Aed" => FALSE, "Untrained" => TRUE, "Normal" => TRUE, "Hod" => TRUE);
    }

    public function accountName()
    {
        $aTeacher = self::$arrTeachers[$this->teacherId];
        return $aTeacher->accname;
    }

    static function getAccName($teacherId)
    {
        /* @var $aTeacher Teacher */
        $aTeacher = self::$arrTeachers[$teacherId];
        return $aTeacher->accname;
    }

}

TeacherCompact::init();
?>
