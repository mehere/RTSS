<?php

ini_set("memory_limit", "512M");
define("NUM_STATES_REQUIRED", 5);

function isAvailable($aTeacher)
{
    /* @var $aTeacher TeacherCompact */
    return $aTeacher->isAvailable();
}

function scheduling(&$visitedStates, ScheduleStateHeap $activeStates, ScheduleStateHeapBest $successStates, ScheduleStateHeapBest $stoppedStates)
{
    /* @var $aState ScheduleState */
    /* @var $firstTeacher TeacherCompact */
    while (!($activeStates->isEmpty()))
    {
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

        $overallAvailability = ReliefLesson::AVAILABILITY_BUSY;
        foreach ($aState->lessonsNotAllocated as $lessonKey => $aLesson)
        {
            $availability = $aLesson->canBeDoneBy($firstTeacher);
            if ($availability < $overallAvailability)
            {
                $overallAvailability = $availability;
            }
            if (($availability == ReliefLesson::AVAILABILITY_FREE) || ($availability == ReliefLesson::AVAILABILITY_SKIPPED))
            {
                $aNewState = clone $aState;
                $aNewState->allocateLessonToFirstTeacher($lessonKey);
                $aNewStateKey = $aNewState->toString();
                if (!isset($visitedStates[$aNewStateKey]))
                {
                    $visitedStates[$aNewStateKey] = NULL;
                    $activeStates->insert($aNewState);
                }
            }
        }

        if ($overallAvailability != ReliefLesson::AVAILABILITY_FREE)
        {
            if ($overallAvailability == ReliefLesson::AVAILABILITY_PARTIAL)
            {
                $aState->teachersStuck[$firstTeacher->accname] = $firstTeacher;
            }
            // skip this teacher
            $aState->removeFirstTeacher();
            $activeStates->insert($aState);
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
$dateString = "2013/2/6";
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

$startTime = microtime(true);
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

    ScheduleState::$arrTeachers = ScheduleState::$arrTeachers + $$varArrTeachers;
    foreach ($$varArrTeachers as $accname => $aTeacher)
    {
        $aCompactTeacher = new TeacherCompact($aTeacher, $aType);
        ${$varArrCompactTeachers}[$accname] = $aCompactTeacher;
    }

    // Applying Leave
    $varArrTeacherLeaves = "arr{$aType}TeachersLeaves";
    $$varArrTeacherLeaves = $arrLeaves[$aType];

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
      //    print_r($$varArrCompactTeachers);
     */
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
      //    print_r($$varArrCompactTeachers);
      //end of print
     */

    foreach ($$varArrTeacherLeaves as $accname => $leaveRecords)
    {
//        echo '<br> accname:'.$accname;
        $aCompactTeacher = ${$varArrCompactTeachers}[$accname];
        $aTeacher = ${$varArrTeachers}[$accname];
        $someLessonsNeedRelief = $aCompactTeacher->onLeave($aTeacher, $leaveRecords);
    }
    if (!empty($someLessonsNeedRelief))
    {
        $lessonsNeedRelief = $lessonsNeedRelief + $someLessonsNeedRelief;
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
    $arrGroup1 = $arrGroup1 + $$varArrAvailableTeachers;
}

//uasort($arrGroup1, 'cmpTeachers');
//echo "<br>Group 1<br>";
//print_r($arrGroup1);
//foreach ($lessonsNeedRelief as $aReliefLesson)
//{
//    /* @var $aReliefLesson ReliefLesson */
//    $str = $aReliefLesson->toString();
//    echo "<br>$str";
//}

$visitedStates = array();
$activeStates = new ScheduleStateHeap();
$successStates = new ScheduleStateHeapBest(5);
$stoppedStates = new ScheduleStateHeapBest(5);

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
unset($dateScheduled);
unset($dateString);
unset($group1Types);
unset($leaveRecords);
unset($lessonsNeedRelief);
unset($methodGetTeachers);
unset($scheduler);
unset($someLessonsNeedRelief);
unset($startState);
unset($teacherFilter);
unset($typesOfTeachers);
unset($varArrAvailableTeachers);
unset($varArrCompactTeachers);
unset($varArrExcludedTeachers);
unset($varArrTeacherLeaves);
unset($varArrTeachers);

scheduling($visitedStates, $activeStates, $successStates, $stoppedStates);

$endTime = microtime(true);
//echo "<br>visited:<br>";
//print_r($visitedStates);
echo "<br>Memory:";
echo memory_get_peak_usage(), "\n";

echo "<br>Time:";
$timeSpent = $endTime - $startTime;
echo $timeSpent;

echo "<br>";
echo "<br>active:<br>:";
//print_r($activeStates);
echo "<br>";
echo "<br>:success<br>:";
print_r($successStates);
echo "<br>";
echo "<br>stopped:<br>:";
//print_r($stoppedStates);

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
        $aState->resetAndAddTeachers($arrGroup2);
        $activeStates->insert($aState);
    }
    $stoppedStates = new ScheduleStateHeapBest(5);
    scheduling($visitedStates, $activeStates, $successStates, $stoppedStates);
}
if ($successStates->numberStates > 0){
    //
}
else {
    // failure
}

?>
