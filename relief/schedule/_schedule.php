<?php

var_dump($_POST);
exit;


ini_set("memory_limit", "512M");
define("NUM_STATES_REQUIRED", 3);
define("TIME_TO_WAIT", 10);

spl_autoload_register(function($class){
    require_once "../../class/$class.php";
});

Template::validate(true);

function scheduling(&$visitedStates, ScheduleStateHeap $activeStates, ScheduleStateHeapBest $successStates, ScheduleStateHeapBest $stoppedStates)
{
    /* @var $aState ScheduleState */
    /* @var $firstTeacher TeacherCompact */
    global $startTime;
    while (!($activeStates->isEmpty()))
    {
        $nowTime = microtime(true);
        if ((($successStates->numberStates > 0) || ($stoppedStates->numberStates > 0)) && (($nowTime - $startTime) > TIME_TO_WAIT))
        {
//            echo "broken due to time";
            break;
        }
//        error_log($activeStates->count());
//        echo "<br>";
//        echo $activeStates->count();
        $aState = $activeStates->extract();
        if ($successStates->isRejected($aState))
        {
            continue;
        }
        if (empty($aState->lessonsNotAllocated))
        {
            $successStates->insert($aState);
            continue;
        }
        $firstTeacher = $aState->teachersAlive->current();
        if (empty($firstTeacher))
        {
            $stoppedStates->insert($aState);
            continue;
        }

        $overallAvailability = TeacherCompact::AVAILABILITY_BUSY;
//        error_log($firstTeacher->getTypeNo());
        foreach ($aState->lessonsNotAllocated as $lessonKey => $aLesson)
        {
            $availability = $firstTeacher->canTeach($aLesson);
            if ($availability < $overallAvailability)
            {
                $overallAvailability = $availability;
            }
            if (($availability == TeacherCompact::AVAILABILITY_FREE) || ($availability == TeacherCompact::AVAILABILITY_SKIPPED))
            {
                $aNewState = clone $aState;
                $aNewState->allocateLessonToFirstTeacher($lessonKey);
                $aNewStateKey = $aNewState->toString();
                if (!isset($visitedStates[$aNewStateKey]))
                {
                    $visitedStates[$aNewStateKey] = NULL;
                    if (!$successStates->isRejected($aNewState))
                    {
                        $activeStates->insert($aNewState);
                    }
                }
            }
        }

//        error_log("Availability: $overallAvailability");
        if ($overallAvailability != TeacherCompact::AVAILABILITY_FREE)
        {
            if ($overallAvailability == TeacherCompact::AVAILABILITY_PARTIAL)
            {
                $aState->teachersStuck[] = $firstTeacher;
            }
            // skip this teacher
            $aState->removeFirstTeacher();
            $activeStates->insert($aState);
        }
    }
}

///-----------------------------------------------------------------------------
$dateString = $_POST["date"];
if (isset($_POST["btnScheduleAll"]))
{
    $typeSchedule = 1;
} else if (isset($_POST["btnScheduleAdhoc"]))
{
    $typeSchedule = 2;
} else
{
    header("/RTSS/index.php");
}

//To-do: to remove hardcoding
//$typeSchedule = 2;

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
    $arrLeaveId = $scheduler->getLeaveIds();
    $arrExcludedTeachers = $scheduler->getExcludedTeachers();

    foreach ($typesOfTeachers as $aType)
    {
        $varArrTeachers = "arr{$aType}Teachers";
        $methodGetTeachers = "get{$aType}Teachers";

        $$varArrTeachers = call_user_func(array($scheduler, $methodGetTeachers));
    }
} catch (DBException $e)
{
    $_SESSION['scheduleError'] = "Database error.";
    header("Location: result.php");
}

// ADHOC SCHEDULING PROCESSING
if ($typeSchedule == 2)
{
    try
    {
        $reliefPlans = AdHocSchedulerDB::getReliefPlan($dateString);
        $skippingPlan = AdHocSchedulerDB::getSkippingPlan($dateString);
        $blockingPlan = AdHocSchedulerDB::getBlockingPlan($dateString);
    } catch (DBException $e)
    {
        $_SESSION['scheduleError'] = "Database error.";
        header("Location: result.php");
    }

    foreach ($skippingPlan as $aReliefLesson)
    {
        $teacherOriginalAccName = $aReliefLesson->teacherOriginal;
        $teacherOriginal = NULL;
        foreach ($typesOfTeachers as $aType)
        {
            $varArrTeachers = "arr{$aType}Teachers";
            if (isset(${$varArrTeachers}[$teacherOriginalAccName]))
            {
                $teacherOriginal = ${$varArrTeachers}[$teacherOriginalAccName];
                break;
            }
        }
        for ($i = $aReliefLesson->startTimeSlot; $i < $aReliefLesson->endTimeSlot; $i++)
        {
            unset($teacherOriginal->timetable[$i]);
        }
    }

    foreach ($reliefPlans as $aReliefLesson)
    {
        /* @var $aReliefLesson ReliefLesson */
        $teacherOriginalAccName = $aReliefLesson->teacherOriginal;
        $teacherReliefAccName = $aReliefLesson->teacherRelief;
        $teacherOriginal = NULL;
        $teacherRelief = NULL;
        foreach ($typesOfTeachers as $aType)
        {
            $varArrTeachers = "arr{$aType}Teachers";
            if (isset(${$varArrTeachers}[$teacherOriginalAccName]))
            {
                $teacherOriginal = ${$varArrTeachers}[$teacherOriginalAccName];
                break;
            }
        }
        foreach ($typesOfTeachers as $aType)
        {
            $varArrTeachers = "arr{$aType}Teachers";
            if (isset(${$varArrTeachers}[$teacherReliefAccName]))
            {
                $teacherRelief = ${$varArrTeachers}[$teacherReliefAccName];
                break;
            }
        }

        /* @var $teacherOriginal Teacher */
        /* @var $teacherRelief Teacher */
        /* @var $originalLesson Lesson */
        $originalLesson = $teacherOriginal->timetable[$aReliefLesson->startTimeSlot];

        if ($aReliefLesson->startTimeSlot != $originalLesson->startTimeSlot)
        {
            $replacementLesson = clone $originalLesson;
            $newLesson = clone $originalLesson;
            $replacementLesson->endTimeSlot = $aReliefLesson->startTimeSlot;
            $newLesson->startTimeSlot = $aReliefLesson->startTimeSlot;
            for ($i = $replacementLesson->startTimeSlot; $i < $replacementLesson->endTimeSlot; $i++)
            {
                $teacherOriginal->timetable[$i] = $replacementLesson;
            }
            for ($i = $newLesson->startTimeSlot; $i < $newLesson->endTimeSlot; $i++)
            {
                $teacherOriginal->timetable[$i] = $newLesson;
            }
            $originalLesson = $newLesson;
        }
        if ($aReliefLesson->endTimeSlot != $originalLesson->endTimeSlot)
        {
            $replacementLesson = clone $originalLesson;
            $newLesson = clone $originalLesson;
            $replacementLesson->startTimeSlot = $aReliefLesson->endTimeSlot;
            $newLesson->endTimeSlot = $aReliefLesson->endTimeSlot;
            for ($i = $replacementLesson->startTimeSlot; $i < $replacementLesson->endTimeSlot; $i++)
            {
                $teacherOriginal->timetable[$i] = $replacementLesson;
            }
            for ($i = $newLesson->startTimeSlot; $i < $newLesson->endTimeSlot; $i++)
            {
                $teacherOriginal->timetable[$i] = $newLesson;
            }
            $originalLesson = $newLesson;
        }

        for ($i = $originalLesson->startTimeSlot; $i < $originalLesson->endTimeSlot; $i++)
        {
            unset($teacherOriginal->timetable[$i]);
            $teacherRelief->timetable[$i] = $originalLesson;
        }
    }

    foreach ($blockingPlan as $aBlockLesson)
    {
        /* @var $aBlockLesson Lesson */
        /* @var $aLesson Lesson */
        $teacherOriginalAccName = $aBlockLesson->teachers;
        $teacherOriginal = NULL;
        foreach ($typesOfTeachers as $aType)
        {
            $varArrTeachers = "arr{$aType}Teachers";
            if (isset(${$varArrTeachers}[$teacherOriginalAccName]))
            {
                $teacherOriginal = ${$varArrTeachers}[$teacherOriginalAccName];
                break;
            }
        }

        for ($i = $aBlockLesson->startTimeSlot; $i < $aBlockLesson->endTimeSlot; $i++)
        {
            if (isset($originalTeacherTimetable[$i]))
            {
                $aLesson = clone $originalTeacherTimetable[$i];
                $aLesson->isMandatory = TRUE;
                $teacherOriginal->timetable[$i] = $aLesson;
            } else
            {
                $teacherOriginal->timetable[$i] = $aBlockLesson;
            }
        }
    }
}






// initialization
$lessonsNeedRelief = array();

$numTeachers = 0;
foreach ($typesOfTeachers as $aType)
{
    $varArrTeachers = "arr{$aType}Teachers";
    $numTeachers += count($$varArrTeachers);
}
TeacherCompact::$arrTeachers = new SplFixedArray($numTeachers);

$teacherId = 0;
foreach ($typesOfTeachers as $aType)
{
    // Creating Compact Teachers
    $varArrTeachers = "arr{$aType}Teachers";
    $varArrCompactTeachers = "arr{$aType}CompactTeachers";
    $$varArrCompactTeachers = array();

    foreach ($$varArrTeachers as $accname => $fullTeacher)
    {
        TeacherCompact::$arrTeachers[$teacherId] = $fullTeacher;
        $aCompactTeacher = new TeacherCompact($teacherId, $aType);
        ${$varArrCompactTeachers}[$accname] = $aCompactTeacher;
        $teacherId++;
    }

    // Applying Leave
    $varArrTeacherLeaves = "arr{$aType}TeachersLeaves";
    $$varArrTeacherLeaves = $arrLeaves[$aType];

    // Current Exclustions
    $varArrExcludedTeachers = "arrExcluded{$aType}Teachers";
    $$varArrExcludedTeachers = $arrExcludedTeachers[$aType];

    foreach ($$varArrTeacherLeaves as $accname => $leaveRecords)
    {
//        echo '<br> accname:'.$accname;
        $aCompactTeacher = ${$varArrCompactTeachers}[$accname];
        $someLessonsNeedRelief = $aCompactTeacher->onLeave($leaveRecords, $$varArrExcludedTeachers);
        if (!empty($someLessonsNeedRelief))
        {
            $lessonsNeedRelief = $lessonsNeedRelief + $someLessonsNeedRelief;
        }
    }


    $varArrAvailableTeachers = "arrAvailable{$aType}Teachers";
    $$varArrAvailableTeachers = $$varArrCompactTeachers;
//    unset($$varArrCompactTeachers);
    // Managing Exclusions
    foreach ($$varArrExcludedTeachers as $accname => $value)
    {
        unset(${$varArrAvailableTeachers}[$accname]);
    }
}

if (empty($lessonsNeedRelief))
{
    //To-Do: No Scheduling
    $_SESSION['scheduleError'] = "No relief required";
    header("Location: result.php");
}

// initialization of groups ----------------------------------------------------
$group1Types = array(
    "Temp",
    "Aed",
    "Untrained",
);

$group2Types = array(
    "Normal",
);
$group3Types = array(
    "Hod"
);


// group 1 scheduling ---------------------------------------------------------
$arrGroup1 = array();
foreach ($group1Types as $aType)
{
    $varArrAvailableTeachers = "arrAvailable{$aType}Teachers";
    $arrGroup1 = $arrGroup1 + $$varArrAvailableTeachers;
    unset($$varArrAvailableTeachers);
}

//foreach ($arrGroup1 as $aCompactTeacher)
//{
//    $accName = TeacherCompact::getAccName($aCompactTeacher->teacherId);
////    echo "<br>$accName";
//}
//uasort($arrGroup1, 'cmpTeachers');
//echo "<br>Group 1<br>";
//print_r($arrGroup1);
//echo "<br> Relief Lessons!!!!";
//foreach ($lessonsNeedRelief as $aReliefLesson)
//{
//    /* @var $aReliefLesson ReliefLesson */
//    $str = $aReliefLesson->toString();
//    echo "<br>$str";
//}


$activeStates = new ScheduleStateHeap();
$successStates = new ScheduleStateHeapBest(NUM_STATES_REQUIRED);
$stoppedStates = new ScheduleStateHeapBest(NUM_STATES_REQUIRED);
$startState = new ScheduleState($arrGroup1, $lessonsNeedRelief);

//unset($aCompactTeacher);
//unset($aTeacher);
//unset($aType);
//unset($accname);
//unset($arrExcludedTeachers);
//unset($arrGroup1);
//unset($arrLeaves);
//unset($group1Types);
//unset($leaveRecords);
//unset($lessonsNeedRelief);
//unset($methodGetTeachers);
//unset($numTeachers);
//unset($scheduler);
//unset($someLessonsNeedRelief);
//unset($teacherFilter);
//unset($typesOfTeachers);
//unset($value);
//unset($varArrAvailableTeachers);
//unset($varArrCompactTeachers);
//unset($varArrExcludedTeachers);
//unset($varArrTeacherLeaves);
//unset($varArrTeachers);


$startTime = microtime(true);
$visitedStates = array();
$activeStates->insert($startState);
$visitedStates[$startState->toString()] = NULL;

scheduling($visitedStates, $activeStates, $successStates, $stoppedStates);

// round 2
if ($successStates->numberStates == 0)
{
    $arrGroup2 = array();
    foreach ($group2Types as $aType)
    {
        $varArrAvailableTeachers = "arrAvailable{$aType}Teachers";
        $arrGroup2 = $arrGroup2 + $$varArrAvailableTeachers;
    }
    foreach ($stoppedStates->heap as $aState)
    {
        $aState->splitLessons();
        $aState->resetTeachers();
        $aState->addTeachers($arrGroup2);
        $activeStates->insert($aState);
    }
    $stoppedStates = new ScheduleStateHeapBest(NUM_STATES_REQUIRED);
    scheduling($visitedStates, $activeStates, $successStates, $stoppedStates);
}

// round 3
if ($successStates->numberStates == 0)
{
    $arrGroup3 = array();
    foreach ($group3Types as $aType)
    {
        $varArrAvailableTeachers = "arrAvailable{$aType}Teachers";
        $arrGroup3 = $arrGroup3 + $$varArrAvailableTeachers;
    }
    foreach ($stoppedStates->heap as $aState)
    {
        $aState->addTeachers($arrGroup3);
        $activeStates->insert($aState);
    }
    $stoppedStates = new ScheduleStateHeapBest(NUM_STATES_REQUIRED);
    scheduling($visitedStates, $activeStates, $successStates, $stoppedStates);
}

if ($successStates->numberStates == 0)
{
    do
    {
        TeacherCompact::$recommendedNoOfLessons++;
        foreach ($stoppedStates->heap as $aState)
        {
            $aState->resetTeachers();
            $activeStates->insert($aState);
        }
        $stoppedStates = new ScheduleStateHeapBest(NUM_STATES_REQUIRED);
        scheduling($visitedStates, $activeStates, $successStates, $stoppedStates);
        if ($successStates->numberStates > 0)
        {
            break;
        }
    } while (TeacherCompact::$recommendedNoOfLessons <= TeacherCompact::MAX_LESSONS);
}


//$endTime = microtime(true);
//echo "<br>Memory:";
//echo memory_get_peak_usage(), "\n";
//
//echo "<br>Time:";
//$timeSpent = $endTime - $startTime;
//echo $timeSpent;
////
//echo "<br>";
//echo "<br>active:<br>:";
//print_r($activeStates);
//echo "<br>";
//echo "<br>:success<br>:";
//print_r($successStates);
//echo "<br>";
//echo "<br>stopped:<br>:";
//print_r($stoppedStates);

/* @var $aState ScheduleState */


if ($successStates->numberStates > 0)
{
    $successResults = array();
    foreach ($successStates->heap as $aState)
    {
        $results = $aState->beautify();
        $successResults[] = $results;
    }
    try
    {
        SchedulerDB::setScheduleResult($typeSchedule, $dateString, $successResults, $arrLeaveId);
//        print_r($successResults);
    } catch (DBException $e)
    {
        // To-Do:
        // Database Error
        $_SESSION['scheduleError'] = "Database error.";
    }
} else
{
    ///To-Do:
    // Failure Case
    $_SESSION['scheduleError'] = "Failed to find a scheduling result.";
}
header("Location: index.php");
?>
