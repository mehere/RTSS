<?php

class TeacherCompact
{

    const TYPE_LESSON = 'Lesson';
    const TYPE_LEAVE = 'Leave';
    const TYPE_NEED_RELIEF = 'Need Relief';
    const TYPE_RELIEVING = 'Relieving';
    const TYPE_OPTIONAL_REPLACED = "Optional Replaced with Relief";
    const TYPE_OPTIONAL_CANCELLED = "Optional Cancelled";
    const TYPE_AWAY = 'Away';
    const TYPE_OPTIONAL = 'Optional';
    const MAX_LESSONS = 14;

    public static $typeMap;
    public static $recommendedNoOfLessons = self::MAX_LESSONS;
    //put your code here
    public $timetable;
    public $accname;
    public $noTeachingPeriod;
    public $netRelived;
    public $specialitySubject;
    public $type;
    public $hasDone;

    public function __construct($aTeacher, $type)
    {
        /* @var $aTeacher Teacher */
        $this->accname = $aTeacher->accname;
        $this->timetable = array();
        $this->type = $type;
        $this->noTeachingPeriod = 0;
        $this->hasDone = FALSE;

        call_user_func(array($this, "construct{$type}Teacher"), $aTeacher);
    }

    private function constructTempTeacher($aTeacher)
    {
        /* @var $aTeacher Teacher */
//        echo 'OK1';

        $this->netRelived
                = $aTeacher->noLessonRelived;
        $availability = $aTeacher->availability;
        $freeSlots = array();
        foreach ($availability as $availableSlot)
        {
            $startIndex = $availableSlot[0];
            $endIndex = $availableSlot[1];
            for ($i = $startIndex; $i < $endIndex; $i++)
            {
                $freeSlots[$i] = true;
            }
        }
        for ($i = 1; $i <= MAX_LESSON; $i++)
        {
            if (!isset($freeSlots))
            {
                $this->timetable[$i] = TeacherCompact::TYPE_AWAY;
            }
        }
    }

    private function constructAedTeacher($aTeacher)
    {
        /* @var $aTeacher Teacher */
        $this->netRelived
                = $aTeacher->noLessonRelived;
        $hisTimetable = $aTeacher->timetable;
        $this->specialitySubject = $aTeacher->speciality;

        foreach ($hisTimetable as $timeIndex => $aLesson)
        {
            /* @var $aLesson Lesson */
            if ($aLesson->isHighlighted)
            {
                $this->timetable[$timeIndex] = self::TYPE_LESSON;
                $this->noTeachingPeriod++;
            } else
            {
                /// what to do what to do...
                $this->timetable[$timeIndex] = self::TYPE_OPTIONAL;
                $this->noTeachingPeriod++;
            }
        }
    }

    private function constructUntrainedTeacher($aTeacher)
    {
        $this->netRelived
                = $aTeacher->noLessonRelived - $aTeacher->noLessonMissed;

        $hisTimetable = $aTeacher->timetable;

        foreach ($hisTimetable as $timeIndex => $aLesson)
        {
            $this->timetable[$timeIndex] = self::TYPE_LESSON;
        }

        $this->noTeachingPeriod = count($hisTimetable);
    }

    private function constructNormalTeacher($aTeacher)
    {
        $this->netRelived
                = $aTeacher->noLessonRelived - $aTeacher->noLessonMissed;

        $hisTimetable = $aTeacher->timetable;

        foreach ($hisTimetable as $timeIndex => $aLesson)
        {
            $this->timetable[$timeIndex] = self::TYPE_LESSON;
        }

        $this->noTeachingPeriod = count($hisTimetable);
    }

    private function constructHodTeacher($aTeacher)
    {
        $this->netRelived
                = $aTeacher->noLessonRelived - $aTeacher->noLessonMissed;

        $hisTimetable = $aTeacher->timetable;

        foreach ($hisTimetable as $timeIndex => $aLesson)
        {
            $this->timetable[$timeIndex] = self::TYPE_LESSON;
        }

        $this->noTeachingPeriod = count($hisTimetable);
    }

    public function onLeave($aTeacher, $leaveRecords)
    {
        if ($this->type == "Normal")
        {
            /* @var $aTeacher Teacher */
            $lessonsNeedRelief = array();
            $accname = $aTeacher->accname;

            foreach ($leaveRecords as $aLeave)
            {
                $startLeaveIndex = $aLeave["startLeave"];
                $endLeaveIndex = $aLeave["endLeave"];

                $lastLesson = null;
                $aReliefLesson = null;

                for ($i = $startLeaveIndex; $i < $endLeaveIndex; $i++)
                {
                    if ((isset($aTeacher->timetable[$i])) &&
                            (!empty($aTeacher->timetable[$i]->classes)))
                    {
                        $aLesson = $aTeacher->timetable[$i];
                        /* @var $aLesson Lesson */

                        if ($aLesson !== $lastLesson)
                        {
                            ScheduleState::$arrLesson[$aLesson->lessonId] = $aLesson;
                            $aReliefLesson = new ReliefLesson($accname, $aLesson->lessonId, $i);
                            $lessonsNeedRelief[$aReliefLesson->toString()] = $aReliefLesson;
                        } else
                        {
                            $aReliefLesson->incrementEndTime();
                        }

                        $this->timetable[$i] = TeacherCompact::TYPE_NEED_RELIEF;
                        $this->noTeachingPeriod--;
                    } else
                    {
                        $aLesson = null;
                        $aReliefLesson = null;
                        $this->timetable[$i] = self::TYPE_LEAVE;
                    }

                    $lastLesson = $aLesson;
                }
            }


            return $lessonsNeedRelief;
        }
    }

    public function isAvailable()
    {
        $filteredTimetable = array_filter($this->timetable, 'isOptional');
        $noBusyLesson = count($filteredTimetable);
        if ($noBusyLesson < self::$recommendedNoOfLessons)
        {
            return true;
        } else
        {
            return false;
        }
    }

    // return bool: hasToSkipLesson
    public function setLesson($timeIndex)
    {
        $hasToSkipLesson = FALSE;
        if (isset($this->timetable[$timeIndex]))
        {
            $aLesson = $this->timetable[$timeIndex];

            if ($aLesson == TeacherCompact::TYPE_OPTIONAL)
            {
                $hasToSkipLesson = TRUE;
            }
        }
        if ($hasToSkipLesson)
        {
            $this->timetable[$timeIndex] = TeacherCompact::TYPE_OPTIONAL_REPLACED;
        } else
        {
            $this->timetable[$timeIndex] = TeacherCompact::TYPE_RELIEVING;
            $this->noTeachingPeriod++;
            $this->netRelived++;
        }
        return $hasToSkipLesson;
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

    public function getTypeNo(){
        return self::$typeMap[$this->type];
    }

    private function isOptional($aLesson)
    {
        return ($aLesson != self::TYPE_OPTIONAL) ? TRUE : FALSE;
    }

    static function init()
    {
        self::$typeMap = array("Temp" => 1, "Aed" => 1, "Untrained" => 1, "Normal" => 2, "Aed" => 3);
    }


}

TeacherCompact::init();
?>
