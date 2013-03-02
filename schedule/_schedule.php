<?php

ini_set("memory_limit", "512M");
define("NUM_STATES_REQUIRED", 3);
define("TIME_TO_WAIT", 10);

require_once '../../php-head.php';

spl_autoload_register(
        function ($class)
        {
            include '../../class/' . $class . '.php';
        });
header("Expires: 0");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");

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
$typeSchedule = 0;
if (isset($_POST["btnScheduleAll"]))
{
    $typeSchedule = 1;
} else if (isset($_POST["btnScheduleAdhoc"]))
{
    $typeSchedule = 2;
}

$dateScheduled = DateTime::createFromFormat(PageConstant::DATE_FORMAT_ISO, $dateString);

$typesOfTeachers = array(
    "Temp",
    "Aed",
    "Untrained",
    "Normal",
    "Hod"
);

$startTime = microtime(true);
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
    //echo $e->getMessage();
    echo $e;
    exit();
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


    // Printing for Debugging
/*
    echo "<br><br>Type: $aType";
    echo "<br>Leaves<br>";
    print_r($arrLeaves[$aType]);
    echo "<br>Teachers: <br>";
    foreach ($$varArrTeachers as $key => $aTeacher)
    {
        echo "Key: $key<br>";
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
 *
 *
 */
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

foreach ($arrGroup1 as $aCompactTeacher){
    $accName = TeacherCompact::getAccName($aCompactTeacher->teacherId);
//    echo "<br>$accName";
}

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

$visitedStates = array();
$activeStates = new ScheduleStateHeap();
$successStates = new ScheduleStateHeapBest(NUM_STATES_REQUIRED);
$stoppedStates = new ScheduleStateHeapBest(NUM_STATES_REQUIRED);

$startState = new ScheduleState($arrGroup1, $lessonsNeedRelief);
$activeStates->insert($startState);
$visitedStates[$startState->toString()] = NULL;

unset($aCompactTeacher);
unset($aTeacher);
unset($aType);
unset($accname);
unset($arrExcludedTeachers);
unset($arrGroup1);
unset($arrLeaves);
//unset($dateScheduled);
//unset($dateString);
unset($group1Types);
unset($leaveRecords);
unset($lessonsNeedRelief);
unset($methodGetTeachers);
unset($numTeachers);
unset($scheduler);
unset($someLessonsNeedRelief);
unset($startState);
unset($teacherFilter);
unset($typesOfTeachers);
unset($value);
unset($varArrAvailableTeachers);
unset($varArrCompactTeachers);
unset($varArrExcludedTeachers);
unset($varArrTeacherLeaves);
unset($varArrTeachers);

scheduling($visitedStates, $activeStates, $successStates, $stoppedStates);
//$stoppedStates->insert($startState);
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


$endTime = microtime(true);
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

//    print_r($successResults);
    try
    {
        SchedulerDB::setScheduleResult($successResults, $dateString);        
    } catch (DBException $e)
    {
        // To-Do:
        // Database Error
        $_SESSION['scheduleError']="Database error.";
    }
} else
{
    ///To-Do:
    // Failure Case
    $_SESSION['scheduleError']="Failed to find a scheduling result.";
}

header("Location: result.php");
?>