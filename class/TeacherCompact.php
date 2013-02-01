<?php

class TeacherCompact
{

    const TYPE_LESSON = 'Lesson';
    const TYPE_LEAVE = 'Leave';
    const TYPE_NEED_RELIEF = 'Need Relief';
    const TYPE_RELIEVING = 'Relieving';
    const MAX_LESSONS = 14;

    //put your code here
    public $timetable;
    public $accname;
    public $noTeachingPeriod;
    public $noSessionRelievedNet;
    public $teacherType;
    public $specialitySubjects;


    public function __construct($aTeacher)
    {
        /* @var $aTeacher Teacher */
        $this->accname = $aTeacher->accname;
        $this->noSessionRelievedNet
                = $aTeacher->$noLessonRelived - $aTeacher->$noLessonMissed;

        $hisTimetable = $aTeacher->timetable;

        foreach ($hisTimetable as $timeIndex => $aLesson)
        {
            $this->timetable[$timeIndex] = TeacherCompact::TYPE_LESSON;
        }

        $this->noTeachingPeriod = count($hisTimetable);
    }

    public function needRelief($aTeacher)
    {
        /* @var $aTeacher Teacher */
        $lessonsNeedRelief = array();
        $leaves = $aTeacher->leave;
        $accname = $aTeacher->accname;

        foreach ($leaves as $aLeave)
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
                    if ($aLesson !== $lastLesson)
                    {
                        $aReliefLesson = new ReliefLesson($aLesson, $i);
                        $lessonsNeedRelief[$aLesson . " - $accname"] = $aReliefLesson;
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

    public function isAvailable(){
        $noBusyLesson = count($this->timetable);
        if ($noBusyLesson < self::MAX_LESSONS){
            return true;
        }
        else {
            return false;
        }
    }

}

?>
