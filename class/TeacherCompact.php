<?php

class TeacherCompact
{

    const TYPE_LESSON = 'Lesson';
    const TYPE_LEAVE = 'Leave';
    const TYPE_NEED_RELIEF = 'Need Relief';
    const TYPE_RELIEVING = 'Relieving';
    const TYPE_AWAY = 'Away';
    const TYPE_OPTIONAL = 'Optional';
    const MAX_LESSONS = 14;

    //put your code here
    public $timetable;
    public $accname;
    public $noTeachingPeriod;
    public $noSessionRelievedNet;
    public $teacherType;
    public $specialitySubjects;
    public $type;

    public function __construct($aTeacher, $type)
    {
        /* @var $aTeacher Teacher */
        $this->accname = $aTeacher->accname;
        $this->timetable = array();
        $this->type = $type;
        $this->noTeachingPeriod = 0;
        call_user_func("construct{$type}Teacher", $aTeacher);
    }

    private function constructTempTeacher($aTeacher)
    {
        $this->noSessionRelievedNet
                = $aTeacher->$noLessonRelived;
        $availability = $aTeacher->availability;
        $freeSlots = array();
        foreach ($availability as $availableSlot)
        {
            $startIndex = $availableSlot[0];
            $endIndex = $availableSlot[1];
            for ($i = $startIndex; $i < $endIndex; $i++)
            {
                $freeSlots[i] = true;
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
        $this->noSessionRelievedNet
                = $aTeacher->$noLessonRelived;
        $hisTimetable = $aTeacher->timetable;
        $this->specialitySubjects = $aTeacher->speciality;

        foreach ($hisTimetable as $timeIndex => $aLesson)
        {
            /* @var $aLesson Lesson */
            if ($aLesson->isHighlighted)
            {
                $this->timetable[$timeIndex] = TeacherCompact::TYPE_LESSON;
                $this->noTeachingPeriod++;
            } else
            {
                /// what to do what to do...
                $this->timetable[$timeIndex] = TeacherCompact::TYPE_OPTIONAL;
            }
        }
    }

    private function constructUntrainedTeacher($aTeacher)
    {
        $this->noSessionRelievedNet
                = $aTeacher->$noLessonRelived;
    }

    private function constructNormalTeacher($aTeacher)
    {
        $this->noSessionRelievedNet
                = $aTeacher->$noLessonRelived - $aTeacher->$noLessonMissed;

        $hisTimetable = $aTeacher->timetable;

        foreach ($hisTimetable as $timeIndex => $aLesson)
        {
            $this->timetable[$timeIndex] = TeacherCompact::TYPE_LESSON;
        }

        $this->noTeachingPeriod = count($hisTimetable);
    }

    public function onLeave($leaveRecords, $aTeacher = null)
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
                    if (isset($aTeacher->timetable[$i]))
                    {
                        $aLesson = $aTeacher->timetable[$i];
                        /* @var $aLesson Lesson */
                        if ($aLesson !== $lastLesson)
                        {
                            $aReliefLesson = new ReliefLesson($aLesson, $i);
                            $lessonsNeedRelief[$aLesson->lessonId . " - $accname"] = $aReliefLesson;
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
        $noBusyLesson = count($this->timetable);
        if ($noBusyLesson < self::MAX_LESSONS)
        {
            return true;
        } else
        {
            return false;
        }
    }

}

?>
