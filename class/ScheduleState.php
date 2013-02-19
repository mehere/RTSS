<?php

class ScheduleState
{

    const COST_TYPE_1 = 250;
    const COST_TYPE_2 = 4000;
    const COST_TYPE_3 = 65000;
    const COST_TYPE_0 = 1000000;
    const COST_SKIP_LESSON = 15;
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
//    public $factor1_fairnessCost;
    public $factor2_hassleCost;
    public $noGrp1;
    public $noGrp2;
    public $noGrp3;
    public $baseCostIndex;

    public function __construct($teachersAlive, $lessonsNotAllocated)
    {
        $this->teachersAlive = new TeacherCompactHeap();
        $this->teachersStuck = array();
        $this->lessonsAllocated = array();
        $this->lessonsNotAllocated = array();

        foreach ($teachersAlive as $aTeacher)
        {
            $this->teachersAlive->insert($aTeacher);
        }
        foreach ($lessonsNotAllocated as $key => $value)
        {
            $this->lessonsNotAllocated[$key] = $value;
        }

        $this->noGrp1 = count($teachersAlive);
        $this->noGrp2 = 0;
        $this->noGrp3 = 0;

        $this->baseCostIndex = 1;
        $this->actualIncurredCost = 0;
        $baseCost = constant("ScheduleState::COST_TYPE_$this->baseCostIndex");
//        echo "$baseCost";
        $this->estimatedFutureCost = count($this->lessonsNotAllocated) * $baseCost;
        $this->expectedTotalCost = $this->actualIncurredCost + $this->estimatedFutureCost;
    }

    public function toString()
    {
        $stateString = "";
        foreach ($this->lessonsAllocated as $aReliefLesson)
        {
            /* @var $aReliefLesson ReliefLesson */
            $stateString = $stateString . $aReliefLesson->toString() . "; ";
        }
        return $stateString;
    }

    public function __clone()
    {
        $this->teachersAlive = clone $this->teachersAlive;
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
        ksort($this->lessonsAllocated);

        // setting status of teacher
        $firstTeacher = clone ($this->teachersAlive->extract());
        $fullTeacher = self::$arrTeachers[$firstTeacher->accname];
        $numberLessonSkipped = 0;

        $firstTeacher->hasDone = TRUE;
        for ($i = $aLesson->startTimeSlot; $i < $aLesson->endTimeSlot; $i++)
        {
            $hasSkipped = $firstTeacher->setLesson($i);
            if ($hasSkipped)
            {
                $numberLessonSkipped++;
            } else
            {
//                $this->factor1_fairnessCost += $firstTeacher->netRelived;
            }
        }
        $numberLessonSkipped += $firstTeacher->cancelExcess();

        // setting status of lesson
        $aLesson->teacherRelief = $firstTeacher->accname;
        $teacherType = $firstTeacher->getTypeNo();

        // caluculating costs --------------------------------------------------
        $typeCost = constant("ScheduleState::COST_TYPE_$teacherType");

        // cost for skipping lessons:
        $skippingCost = $numberLessonSkipped * ScheduleState::COST_SKIP_LESSON;

        // subject cost
        $subjectCost = 0;
        $fullLesson = self::$arrLesson[$aLesson->lessonId];
        if ($fullLesson->subject != $fullTeacher->speciality)
        {
            $subjectCost = ScheduleState::COST_SUBJECT_UNFAMILAR;
        }

        // class cost
        $classCost = ScheduleState::COST_CLASS_UNFAMILAR;

        $aClass = key($fullLesson->classes);
        if (isset($fullTeacher->classes[$aClass]))
        {
            $classCost = 0;
        }

        $this->actualIncurredCost += $typeCost;
        $this->actualIncurredCost += $skippingCost;
        $this->actualIncurredCost += $classCost;

        $baseCost = constant("ScheduleState::COST_TYPE_$this->baseCostIndex");
        $this->estimatedFutureCost = count($this->lessonsNotAllocated) * $baseCost;
        $this->expectedTotalCost = $this->actualIncurredCost + $this->estimatedFutureCost;

        //To-Do: to check if available
//        $this->teachersAlive->insert($firstTeacher);
        if ($firstTeacher->isAvailable())
        {
            $this->teachersAlive->insert($firstTeacher);
        }
//        echo "<br>Cost: $this->expectedTotalCost <br><br>";
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
        $aTeacher = $this->teachersAlive->extract();
        $typeNo = $aTeacher->getTypeNo();

        $propertyName = "noGrp$typeNo";
        $this->$propertyName--;

        $this->baseCostIndex = 0;
        for ($i = 1; $i <= 3; $i++)
        {
            $propertyName = "noGrp$i";
            if ($this->$propertyName > 0)
            {
                $this->baseCostIndex = $i;
                break;
            }
        }
        $baseCost = constant("ScheduleState::COST_TYPE_$this->baseCostIndex");
        $this->estimatedFutureCost = count($this->lessonsNotAllocated) * $baseCost;
        $this->expectedTotalCost = $this->actualIncurredCost + $this->estimatedFutureCost;
    }

}

?>
