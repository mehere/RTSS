<?php

function isAvailable($aTeacher)
{
    /* @var $aTeacher TeacherCompact */
    return $aTeacher->isAvailable();
}

function cmpTeachers($teacher1, $teacher2)
{
    /* @var $teacher1 TeacherCompact */
    /* @var $teacher2 TeacherCompact */
    $type1 = $teacher1->getTypeNo();
    $type2 = $teacher2->getTypeNo();
    if ($type1 != $type2)
    {
        return ($type1 < $type2) ? -1 : 1;
    }

    // 1st sort: Teaching Periods
    $noTeachingPeriod1 = $teacher1->noTeachingPeriod;
    $noTeachingPeriod2 = $teacher2->noTeachingPeriod;
    if ($noTeachingPeriod1 != $noTeachingPeriod2)
    {
        return ($noTeachingPeriod1 < $noTeachingPeriod2) ? -1 : 1;
    }

    // 2nd sort: Done Before
    $hasDone1 = $teacher1->hasDone;
    $hasDone2 = $teacher2->hasDone;
    if ($hasDone1 != $hasDone2)
    {
        return ($hasDone1 === FALSE) ? -1 : 1;
    }

    // 3rd sort: Net Relief
    $netRelived1 = $teacher1->netRelived;
    $netRelived2 = $teacher2->netRelived;
    if ($netRelived1 != $netRelived2)
    {
        return ($netRelived1 < $netRelived2) ? -1 : 1;
    }

    return 0;
}

function cmpStates($state1, $state2)
{
    /* @var $state1 ScheduleState */
    /* @var $state2 ScheduleState */
    $expectedTotalCost1 = $state1->expectedTotalCost;
    $expectedTotalCost2 = $state2->expectedTotalCost;
    if ($expectedTotalCost1 != $expectedTotalCost2)
    {
        return ($expectedTotalCost1 < $expectedTotalCost2) ? -1 : 1;
    }

    $factor1_fairnessCost1 = $state1->factor1_fairnessCost;
    $factor1_fairnessCost2 = $state2->factor1_fairnessCost;
    if ($factor1_fairnessCost1 != $factor1_fairnessCost2)
    {
        return ($factor1_fairnessCost1 < $factor1_fairnessCost2 ) ? -1 : 1;
    }

    $factor2_hassleCost1 = $state1->factor2_hassleCost;
    $factor2_hassleCost2 = $state2->factor2_hassleCost;
    if ($factor2_hassleCost1 != $factor2_hassleCost2)
    {
        return ($factor2_hassleCost1 > $factor2_hassleCost2) ? -1 : 1;
    }

    return 0;
}

function scheduling($visitedStates, $activeStates, $successStates, $stoppedStates)
{
    $breakingScore = NULL;
    echo "<br><br>Active States:<br>";
    print_r($activeStates);
    // group 1 scheduling
    while (!empty($activeStates))
    {
        print_r(count($activeStates));
        echo "<br><br>";
        $aState = current($activeStates);
        $aStateKey = key($activeStates);
        /* @var $aState ScheduleState */
        /* @var $firstTeacher TeacherCompact */
        $firstTeacher = current($aState->teachersAlive);
        if (empty($firstTeacher))
        {
            echo "<br>first teacher is empty<br>";
            $aState = array_shift($activeStates);
            $stoppedStates[$aState->toString()] = $aState;
            continue;
        }

        $lessonsNotAllocated = $aState->lessonsNotAllocated;
        if (!empty($breakingScore))
        {
            if ($breakingScore < $aState->expectedTotalCost)
            {
                echo "Break here";
                break;
            }
        }
        if (empty($lessonsNotAllocated))
        {
            echo "No more lessons not allocated";
            $breakingScore = $aState->expectedTotalCost;
            $successStates[$aStateKey] = $aState;
            array_shift($activeStates);
            continue;
        }
        $newStates = array();
        $overallAvailability = ReliefLesson::AVAILABILITY_BUSY;
        foreach ($lessonsNotAllocated as $key => $aLesson)
        {
            echo "<br><br>creating $key<br>";
            /* @var $aLesson ReliefLesson */
            $availability = $aLesson->canBeDoneBy($firstTeacher);
            if ($availability == ReliefLesson::AVAILABILITY_FREE)
            {
                $overallAvailability = $availability;
                $aNewState = clone $aState;
                $aNewState->allocateLessonToFirstTeacher($key);
                $newStates[$aNewState->toString()] = $aNewState;
            } else if (($availability == ReliefLesson::AVAILABILITY_SKIPPED) && ($overallAvailability != ReliefLesson::AVAILABILITY_FREE))
            {
                $overallAvailability = $availability;
            } else if (($availability == ReliefLesson::AVAILABILITY_PARTIAL) && ($overallAvailability == ReliefLesson::AVAILABILITY_BUSY))
            {
                $overallAvailability = $availability;
            }
        }
        if ((overallAvailability == ReliefLesson::AVAILABILITY_FREE) || (overallAvailability == ReliefLesson::AVAILABILITY_SKIPPED))
        {
            $performed = FALSE;
            foreach ($newStates as $key => $aNewState)
            {
                if (!isset($visitedStates[$key]))
                {
                    $visitedStates[$key] = TRUE;
                    $performed = true;
                    $activeStates[$key] = $aNewState;
                }
            }
            if ($performed)
            {
                uasort($activeStates, 'cmpStates');
            }
            array_shift($activeStates);
        }
        if (overallAvailability != ReliefLesson::AVAILABILITY_FREE)
        {
            if ($overallAvailability == ReliefLesson::AVAILABILITY_PARTIAL)
            {
                $teachersStuck = $aState->teachersStuck;
                $teachersStuck[$firstTeacher->accname] = $firstTeacher;
            }
            // skip this teacher
            $aState->removeFirstTeacher;

            if (!empty($aState->teachersAlive))
            {
                uasort($activeStates, 'cmpStates');
            } else
            {
                $stoppedStates[$aStateKey] = $aState;
            }
        }
    }
}

include_once '../../constant.php';
// Start ----------------------------------------------------------------------
spl_autoload_register(
        function ($class)
        {
            include '../../class/' . $class . '.php';
        });


//$dateScheduledString = $_POST["date"];
//$typeSchedule;
//if (array_key_exists("btnScheduleAll", $_POST))
//{
//    $typeSchedule = 1;
//} else if (array_key_exists("btnScheduleAdhoc", $_POST))
//{
//    $typeSchedule = 2;
//}
//
//echo "<br>type: $typeSchedule";
//
///// To-DO: format to be soft-coded
//$format = "Y-m-d";
//$dateScheduled = DateTime::createFromFormat($format, $dateScheduledString);
/* @var $dateScheduled DateTime */

//echo "<br>Day: $day";
//junk
$dateString = "2013-2-6";
$dateScheduled = DateTime::createFromFormat(PageConstant::DATE_FORMAT_ISO, $dateString);

$typesOfTeachers = array(
    "Temp",
    "Aed",
    "Untrained",
    "Normal",
    "Hod"
);

// calling scheduler DB
try
{
    $scheduler = new SchedulerDB($dateScheduled);
    TeacherCompact::$recommendedNoOfLessons = $scheduler->getRecommendedNoOfLessons();
    $arrLeaves = $scheduler->getLeave();
    $arrExcludedTeachers = $scheduler->getExcludedTeachers();

    foreach ($typesOfTeachers as $aType)
    {
        $varArrTeachers = "arr{$aType}Teachers";
        $methodGetTeachers = "get{$aType}Teachers";

        $$varArrTeachers = call_user_func(array($scheduler, $methodGetTeachers));
    }
} catch (DBException $e)
{
    echo "DB Error";
    echo $e->getMessage();
    exit();
}


// initialization
ScheduleState::$arrTeachers = array();
$lessonsNeedRelief = array();
$teacherFilter = function($excludedList)
        {
            return function($aCompactTeacher) use($excludedList)
                    {
                        /* @var $aCompactTeacher TeacherCompact */
                        $accname = $aCompactTeacher->accname;
                        if (isset($excludedList[$accname]))
                            return FALSE;

                        return $aCompactTeacher->isAvailable();
                    };
        };


foreach ($typesOfTeachers as $aType)
{
    // Creating Compact Teachers
    $varArrTeachers = "arr{$aType}Teachers";
    $varArrCompactTeachers = "arr{$aType}CompactTeachers";
    $$varArrCompactTeachers = array();

    array_merge(ScheduleState::$arrTeachers, $$varArrTeachers);
    foreach ($$varArrTeachers as $accname => $aTeacher)
    {
        $aCompactTeacher = new TeacherCompact($aTeacher, $aType);
        ${$varArrCompactTeachers}[$accname] = $aCompactTeacher;
    }


    // Applying Leave
    $varArrTeacherLeaves = "arr{$aType}TeachersLeaves";
    $$varArrTeacherLeaves = $arrLeaves[$aType];

    echo "<br><br>Type: $aType";
    echo "<br>Leaves<br>";
    print_r($arrLeaves[$aType]);
    echo "<br>Teachers: <br>";
    foreach ($$varArrTeachers as $aTeacher)
    {
        print_r($aTeacher);
        echo "<br>";
    }
//    print_r($$varArrTeachers);
    echo "<br>Compact Teacher:<br>";
    foreach ($$varArrCompactTeachers as $aTeacher)
    {
        print_r($aTeacher);
        echo "<br>";
    }
//    print_r($$varArrCompactTeachers);

    foreach ($$varArrTeacherLeaves as $accname => $leaveRecords)
    {
//        echo '<br> accname:'.$accname;
        $aCompactTeacher = ${$varArrCompactTeachers}[$accname];
        $aTeacher = ${$varArrTeachers}[$accname];
        $someLessonsNeedRelief = $aCompactTeacher->onLeave($aTeacher, $leaveRecords);
    }
    if (!empty($someLessonsNeedRelief))
    {
        $lessonsNeedRelief = array_merge($lessonsNeedRelief, $someLessonsNeedRelief);
    }

    // Managing Exclusions
    $varArrExcludedTeachers = "arrExcluded{$aType}Teachers";
    $$varArrExcludedTeachers = $arrExcludedTeachers[$aType];

    // Getting list of teachers who are not excluded, not exceeding time slots
    $varArrAvailableTeachers = "arrAvailable{$aType}Teachers";
    $$varArrAvailableTeachers = array_filter($$varArrCompactTeachers, $teacherFilter($$varArrExcludedTeachers));
}

// initialization of groups ----------------------------------------------------
$group1Types = array(
    "Temp",
    "Aed",
    "Untrained",
);

$group2Types = array(
    "Normal",
    "Hod"
);


// group 1 scheduling ---------------------------------------------------------
$arrGroup1 = array();
foreach ($group1Types as $aType)
{
    $varArrAvailableTeachers = "arrAvailable{$aType}Teachers";
    $arrGroup1 = array_merge($arrGroup1, $$varArrAvailableTeachers);
}

uasort($arrGroup1, 'cmpTeachers');
echo "<br>Group 1<br>";
//print_r($arrGroup1);
foreach ($lessonsNeedRelief as $aReliefLesson)
{
    /* @var $aReliefLesson ReliefLesson */
    $str = $aReliefLesson->toString();
    echo "<br>$str";
}

$visitedStates = array();
$activeStates = array();
$successStates = array();
$stoppedStates = array();

$startState = new ScheduleState($arrGroup1, $lessonsNeedRelief);
$activeStates[$startState->toString()] = $startState;
$visitedStates[$startState->toString()] = TRUE;



scheduling($visitedStates, $activeStates, $successStates, $stoppedStates);

print_r($lessonsNeedRelief);
die;

if (empty($successStates))
{
    $arrGroup2 = array();
    foreach ($group2Types as $aType)
    {
        $varArrAvailableTeachers = "arrAvailable{$aType}Teachers";
        $arrGroup2 = array_merge($arrGroup2, $$varArrAvailableTeachers);
    }
    foreach ($stoppedStates as $aState)
    {
        $aState->splitLessons();
        $aState->resetAndAddTeachers($arrGroup2);
    }
    $activeStates = $stoppedStates;
    $stoppedStates = array();
    scheduling($visitedStates, $activeStates, $successStates, $stoppedStates);

    if (empty($successStates))
    {

    }
}
?>
