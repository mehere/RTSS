<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ScheduleState
 *
 * @author Wee
 */
class ScheduleState
{

    const COST_TYPE_1 = 100;
    const COST_TYPE_2 = 2500;
    const COST_TYPE_3 = 50000;
    const COST_SKIP_LESSON = 1;
    const COST_SUBJECT_UNFAMILAR = 1;
    const COST_CLASS_UNFAMILAR = 1;

    public static $arrTeachers;
    public static $arrLesson;
    public $teachersAlive;
    public $teachersStuck;
    public $lessonsNotAllocated;
    public $lessonsAllocated;
    public $expectedTotalCost;
    public $actualIncurredCost;
    public $estimatedFutureCost;
    public $factor1_fairnessCost;
    public $factor2_hassleCost;
    public $noGrp1;
    public $noGrp2;
    public $noGrp3;
    public $baseCostIndex;

    public function __construct($teachersAlive, $lessonsNotAllocated)
    {
        $this->teachersAlive = array();
        $this->teachersStuck = array();
        $this->lessonsAllocated = array();
        $this->lessonsNotAllocated = array();

        foreach ($teachersAlive as $key => $value)
        {
            $this->teachersAlive[$key] = clone $value;
        }
        foreach ($lessonsNotAllocated as $key => $value)
        {
            $this->lessonsNotAllocated[$key] = clone $value;
        }

        $this->noGrp1 = count($teachersAlive);
        $this->noGrp2 = 0;
        $this->noGrp3 = 0;

        $this->baseCostIndex = 1;
    }

    public function toString()
    {
        $stateString = "";
        foreach ($this->lessonsAllocated as $aReliefLesson)
        {
            /* @var $aReliefLesson ReliefLesson */
            $stateString = $stateString . $aReliefLesson->toString() . "; ";
        }
    }

    public function __clone()
    {
        $this->teachersAlive = ScheduleState::cloneArray($this->teachersAlive);
        $this->teachersStuck = ScheduleState::cloneArray($this->teachersStuck);
        $this->lessonsAllocated = ScheduleState::cloneArray($this->lessonsAllocated);
        $this->lessonsNotAllocated = ScheduleState::cloneArray($this->lessonsNotAllocated);
    }

    public static function cloneArray($arr)
    {
        $newArr = array();
        foreach ($arr as $key => $value)
        {
            $newArr[$key] = clone $value;
        }
        return $newArr;
    }

    public function allocateLessonToFirstTeacher($key)
    {
        /* @var $aLesson ReliefLesson */
        /* @var $firstTeacher TeacherCompact */
        /* @var $fullTeacher Teacher */
        /* @var $fullLesson Lesson */

        // moving from unallocated to allocated
        $aLesson = $this->lessonsNotAllocated[$key];
        unset($this->lessonsNotAllocated[$key]);
        $this->lessonsAllocated[$key] = $aLesson;

        // setting status of teacher
        $firstTeacher = current($this->teachersAlive);
        $fullTeacher = self::$arrTeachers[$firstTeacher->accname];
        $numberLessonSkipped = 0;

        for ($i = $aLesson->startTimeSlot; $i < $aLesson->endTimeSlot; $i++)
        {
            $hasSkipped = $firstTeacher->setLesson($i);
            if ($hasSkipped)
            {
                $numberLessonSkipped++;
            } else
            {
                $this->factor1_fairnessCost += $firstTeacher->netRelived;
            }
        }
        $numberLessonSkipped += $firstTeacher->cancelExcess();

        // setting status of lesson
        $aLesson->teacherRelief = $firstTeacher;
        $teacherType = $firstTeacher->getTypeNo();

        // caluculating costs --------------------------------------------------
        $typeCost = constant("ScheduleState::COST_TYPE_$teacherType");

        // cost for skipping lessons:
        $skippingCost = $numberLessonSkipped * ScheduleState::COST_SKIP_LESSON;

        // subject cost
        $subjectCost = 0;
        $fullLesson = self::$arrLesson[$aLesson->lessonId];

        if ($fullLesson->subject != $firstTeacher->specialitySubject)
        {
            $subjectCost = ScheduleState::COST_SUBJECT_UNFAMILAR;
        }

        // class cost
        $classCost = ScheduleState::COST_CLASS_UNFAMILAR;
        foreach ($fullLesson->classes as $aClass)
        {
            if (isset($fullTeacher->classes[$aClass]))
            {
                $classCost = 0;
                break;
            }
        }

        $this->actualIncurredCost += $typeCost;
        $this->actualIncurredCost += $skippingCost;
        $this->actualIncurredCost + - $classCost;

        $baseCost = constant("ScheduleState::COST_TYPE_$this->baseCostIndex");
        $this->expectedTotalCost = count($this->lessonsNotAllocated) * $this->baseCost;
    }

    public function splitLessons()
    {
        $lessons = $this->lessonsNotAllocated;
        $newLessons = array();
        foreach ($lessons as $aLesson)
        {
            /* @var $aLesson ReliefLesson */
            for ($i = $aLesson->startTimeSlot; $i < $aLesson->endTimeSlot; $i++)
            {
                $aReliefLesson = new ReliefLesson($aLesson->teacherOriginal, $aLesson->lessonId, $i);
                $newLessons[$aReliefLesson->toString()] = $aReliefLesson;
            }
        }

        $this->lessonsNotAllocated = $newLessons;
    }

    public function resetAndAddTeachers($newTeachers)
    {
        $newTeachers = clone $newTeachers;
        $this->teachersAlive = array_merge($this->teachersStuck, $newTeachers);
    }

    public function removeFirstTeacher()
    {
        /* @var $aTeacher TeacherCompact */
        $aTeacher = array_shift($this->teachersAlive);
        $typeNo = $aTeacher->getTypeNo();

        $propertyName = "noGrp$typeNo";
        $this->$propertyName--;

        if (empty($this->teachersAlive))
        {
            $this->baseCostIndex = -1;
        } else
        {
            for ($i = 1; $i <= 3; $i++)
            {
                $propertyName = "noGrp$i";
                if ($this->$propertyName > 0)
                {
                    $this->baseCostIndex = $i;
                    break;
                }
            }
        }
    }

}

?>
