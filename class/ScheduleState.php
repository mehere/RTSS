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

    public $teachersAlive;
    public $teachersStuck;
    public $lessonsNotAllocated;
    public $lessonsAllocated;
    public $expectedTotalCost;
    public $actualIncurredCost;
    public $estimatedFutureCost;
    public $noGrp1;
    public $noGrp2;
    public $noGrp3;
    public $baseCostIndex;
    public $noLessons;

    public function __construct($teachersAlive, $lessonsNotAllocated)
    {
        /* @var $aLesson ReliefLesson */
        $this->teachersAlive = new TeacherCompactHeap();
        $this->teachersStuck = array();
        $this->lessonsAllocated = array();
        $this->lessonsNotAllocated = array();

        foreach ($teachersAlive as $aTeacher)
        {
            $this->teachersAlive->insert($aTeacher);
        }

        $this->noLessons = 0;
        foreach ($lessonsNotAllocated as $key => $aLesson)
        {
            $this->lessonsNotAllocated[$key] = $aLesson;
            $this->noLessons += $aLesson->endTimeSlot - $aLesson->startTimeSlot;
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
    }

    public function allocateLessonToFirstTeacher($key)
    {
        /* @var $aLesson ReliefLesson */
        /* @var $firstTeacher TeacherCompact */
        /* @var $fullTeacher Teacher */
        /* @var $fullLesson Lesson */

        // moving from unallocated to allocated
        $aLesson = clone $this->lessonsNotAllocated[$key];
        unset($this->lessonsNotAllocated[$key]);
        $this->lessonsAllocated[$key] = $aLesson;
        ksort($this->lessonsAllocated);

        $noLessonAllocated = $aLesson->endTimeSlot - $aLesson->startTimeSlot;
        $this->noLessons -= $noLessonAllocated;

        // setting status of teacher
        $firstTeacher = clone ($this->teachersAlive->extract());
        $fullTeacher = TeacherCompact::$arrTeachers[$firstTeacher->teacherId];
        $numberLessonSkipped = 0;

        $firstTeacher->hasDone = TRUE;
        for ($i = $aLesson->startTimeSlot; $i < $aLesson->endTimeSlot; $i++)
        {
            $hasSkipped = $firstTeacher->setLesson($i);
            if ($hasSkipped)
            {
                $numberLessonSkipped++;
            }
        }
        $numberLessonSkipped += $firstTeacher->cancelExcess();

        // setting status of lesson
        $aLesson->teacherRelief = $firstTeacher->teacherId;
        $teacherType = $firstTeacher->getTypeNo();

        // caluculating costs --------------------------------------------------
        $typeCost = constant("ScheduleState::COST_TYPE_$teacherType");

        // cost for skipping lessons:
        $skippingCost = $numberLessonSkipped * ScheduleState::COST_SKIP_LESSON;

        // subject cost
        $subjectCost = 0;
        $fullLesson = TeacherCompact::$arrLesson[$aLesson->lessonId];
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

        $this->actualIncurredCost += $typeCost * $noLessonAllocated;
        $this->actualIncurredCost += $skippingCost * $noLessonAllocated;
        $this->actualIncurredCost += $classCost * $noLessonAllocated;

        $baseCost = constant("ScheduleState::COST_TYPE_$this->baseCostIndex");
        $this->estimatedFutureCost = $this->noLessons * $baseCost;
        $this->expectedTotalCost = $this->actualIncurredCost + $this->estimatedFutureCost;

        $this->teachersAlive->insert($firstTeacher);
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

    public function resetTeachers()
    {
        /* @var $aTeacher TeacherCompact */
        foreach ($this->teachersStuck as $aTeacher)
        {
            $this->teachersAlive->insert($aTeacher);
            $typeNo = $aTeacher->getTypeNo();
            $propertyName = "noGrp$typeNo";
            $this->$propertyName++;
        }

        $this->teachersStuck = array();
    }

    public function addTeachers($newTeachers)
    {
        foreach ($newTeachers as $aTeacher)
        {
            $this->teachersAlive->insert($aTeacher);
            $typeNo = $aTeacher->getTypeNo();
            $propertyName = "noGrp$typeNo";
            $this->$propertyName++;
        }
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

    public function beautify(){
        /* @var $aLesson ReliefLesson*/
        $results = array();
        foreach ($this->lessonsAllocated as $aLesson){
            $aNewLesson = clone $aLesson;
            $aNewLesson->teacherOriginal = TeacherCompact::getAccName($aLesson->teacherOriginal);
            $aNewLesson->teacherRelief = TeacherCompact::getAccName($aLesson->teacherRelief);
            $results[] = $aNewLesson;
        }
        return $results;
    }

}

?>
