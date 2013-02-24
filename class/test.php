<?php

require_once 'Teacher.php';
require_once 'TimetableAnalyzer.php';
require_once 'SchedulerDB.php';
require_once 'DayTime.php';
require_once 'Lesson.php';
require_once 'TimetableDB.php';
require_once 'ListGenerator.php';
require_once 'DBException.php';

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//Teacher::setExcludingList("2013/01/01", Array('123','2345'));
//print_r(Teacher::getExcludingList("2013/01/02"));


//$lesson_1 = Array(
//    "subject" => "Math",
//    "venue" => "LT 2A",
//    "accname" => "1234567",
//    "time-from" => 1,
//    "time-to" => 4,
//    "day" => 1,
//    "isHighlighted" => true,
//    "class" => Array('1A', '2B', '3C')
//);
//$lesson_2 = Array(
//    "subject" => "Eco",
//    "venue" => null,
//    "accname" => "2323123",
//    "time-from" => 4,
//    "time-to" => 6,
//    "day" => 5,
//    "isHighlighted" => false,
//    "class" => Array('5A', '2B', '3C')
//);
//$input = Array($lesson_1, $lesson_2);
//
//$input=array(array (
//      'accname' => '0142380',
//      'class' => 
//      array (
//        0 => 'c',
//        1 => 'dfl',
//        2 => 'd',
//        3 => 'f',
//      ),
//      'day' => '1',
//      'time-from' => '1',
//      'time-to' => '4',
//      'subject' => 'a',
//      'venue' => 'a',
//      'isHighlighted' => '0',
//    ));

//echo TimetableDB::uploadAEDTimetable($input)?"okay":"no";
 
/*
$test = new DBException("Test Error", __FILE__, __LINE__);
echo $test;
 * 
 */
//********xue : testing
//$scheduling = new SchedulerDB(new DateTime("2013-02-06"));

/*
$result = $scheduling->getLeave();
foreach($result as $key=>$value)
{
    echo $key." : <br>";
    print_r($value);
    echo "<br><br>";
}
 * 
 */
//print_r($scheduling->getExcludedTeachers());
/*
$result = $scheduling->getAedTeachers();
foreach($result as $key=>$value)
{
    echo "S**************************<br>";
    echo "key : ".$key."<br>";
    echo "accname : ".$value->accname."<br>";
    echo "name : ".$value->name."<br>";
    echo "noLessonMissed : ".$value->noLessonMissed."<br>";
    echo "noLessonRelived : ".$value->noLessonRelived."<br>";
    echo "noLessonRelived : ".$value->noLessonRelived."<br>";
    print_r($value->classes);
    echo "<br>";
    echo "speciality : ".$value->speciality."<br>";
    echo "<br>";
    foreach($value->timetable as $t=>$one)
    {
        echo "S &&&&&&&&&&&&&&<br>";
        echo "lesson ID : ".$one->lessonId."<br>";
        echo "lesson subj : ".$one->subject."<br>";
        echo "lesson venue : ".$one->venue."<br>";
        echo "start time : ".$t." : <br>";
        echo "end time : ".$one->endTimeSlot."<br>";
        print_r($one->classes);
        echo "<br>";
        echo "E &&&&&&&&&&&&&&<br>";
    }
    echo "<br>";
    echo "E**************************<br>";
    echo "<br><br><br>";
}
 * 
 */ 
/*
$result = $scheduling->getTempTeachers();
foreach($result as $key=>$value)
{
    echo $key." : <br>";
    echo $value->accname."<br>";
    echo $value->name."<br>";
    echo $value->noLessonRelived."<br>";
    echo print_r($value->availability);
    echo "<br>";
}
 * 
 */
/*
$result = Teacher::getAllTeachers();
foreach($result as $key=>$a_teacher)
{
    echo $key."<br>";
    echo $a_teacher['name']."<br>";
    echo $a_teacher['type']."<br>";
    echo $a_teacher['mobile']."<br>";
    echo "<br>";
}
 * 
 */
//echo Teacher::calculateLeaveSlot("8104329", "2013-01-08 09:15", "2013-01-08 13:15");
//$result = Teacher::getLessonSlotsOfTeacher("8104329");
/*
foreach($result as $key=>$a_result)
{
    echo $key." : ";
    foreach($a_result as $a_slot)
    {
        print_r($a_slot);
    }
    echo "<br>";
}
 * 
 */
        //Teacher::listUnmatchedAbbreName($arrTeachers);
        //Teacher::abbreToFullnameBatchSetup($arrTeachers);

        //$timetableanalyzer = new TimetableAnalyzer("13", '1');
        //$timetableanalyzer->readCsv('teacher.csv');
       /*
        $err_message = TimetableDB::insertTimetable($arrLessons, $arrTeachers);
        foreach($err_message as $key=>$error)
        {
            if(!empty($error))
            {
                echo "Key : ".$key."<br> : ".$error;
            }
        }
        * 
        */ 
        
       
        /*
          Teacher::getTeachersAccnameAndFullname($arrTeachers);
          foreach($arrTeachers as $a_teacher)
          {
          echo $a_teacher->abbreviation."<br>";
          echo $a_teacher->name."<br>";
          echo $a_teacher->accname."<br><br>";
          }
         * 
         */
        //Teacher::insertAbbrMatch(array('AF ADF'=>'2344244'));
        //Teacher::insertAbbrMatch(array('ADE'=>'122333121', 'ASDFASF'=>'434332333','AF ADF'=>'2344244','CDDE'=>'ASFEAF'));
        
        /*
          $query_date = "2013-02-06";
          $teacher_on_leave = Teacher::getTeacherOnLeave($query_date);
          foreach($teacher_on_leave as $a_leave_teacher)
          {
          echo "start<br>";
          echo $a_leave_teacher['accname']."<br>";
          echo $a_leave_teacher['handphone']."<br>";
          echo $a_leave_teacher['fullname']."<br>";
          echo $a_leave_teacher['type']."<br>";
          echo $a_leave_teacher['reason']."<br>";
          echo $a_leave_teacher['remark']."<br>";
          echo $a_leave_teacher['leaveID']."<br>";
          echo ($a_leave_teacher['isVerified']?"YES":"NO")."<br>";
          print_r($a_leave_teacher['datetime']);
          echo "<br>";
          echo ($a_leave_teacher['isScheduled']?"YES":"NO")."<br>";
          echo "end<br><br>";
          }
         * 
         */
         
       /*
          $query_date = "2013-02-06";
          $temp_teacher = Teacher::getTempTeacher($query_date);
          foreach($temp_teacher as $key=>$a_leave_teacher)
          {
            echo "start<br>";
            echo $key."<br>";
            echo $a_leave_teacher['accname']."<br>";
            echo $a_leave_teacher['fullname']."<br>";
            echo $a_leave_teacher['type']."<br>";
            echo $a_leave_teacher['remark']."<br>";
            print_r($a_leave_teacher['datetime']);
            echo "<br>";
            echo $a_leave_teacher['MT']."<br>";
            echo $a_leave_teacher['email']."<br>";
            echo $a_leave_teacher['handphone']."<br>";
            echo "end<br><br>";
          }
        * 
        */
        /*
          $test_result = Teacher::getIndividualTeacherDetail("aie");
          echo $test_result['found']."<br>";
          echo $test_result['ID']."<br>";
          echo $test_result['name']."<br>";
          echo $test_result['gender']."<br>";
          echo $test_result['mobile']."<br>";
          echo $test_result['email']."<br>";

         *
         */
        /*
          $output = Lesson::getLessonsToday("2013-1-15");
          echo $output["success"]?"YES":"NO";
          echo $output["error_msg"];
          foreach ($output["Lessons"] as $key => $value) {
          echo 'Lesson ' . $key . ': <br>';
          echo 'Subject: ' . $value->subject . '<br>';
          echo 'Day: ' . $value->day . '<br>';
          echo 'Start: ' . $value->startTimeSlot . ' End: ' . $value->endTimeSlot . '<br>';
          if (!(empty($value->venue))) {
          echo 'Venue: ' . $value->venue . '<br>';
          }
          echo 'Classes: ';
          foreach ($value->classes as $aClass) {
          echo $aClass->name . "; ";
          }
          echo '<br>';
          echo 'Teacher: ';
          foreach ($value->teachers as $aTeacher) {
          echo $aTeacher->abbreviation . '; ';
          }
          echo '<br>';
          echo '<br>';
          }
          foreach ($output["Teachers"] as $key=>$aTeacher) {


          echo $key . ' : '.$aTeacher->name.' , '.$aTeacher->abbreviation.'<br>';

          }
         *
         */
        /*
          $result = User::login("S0127975J", 'S0127975J');
          echo $result['accname'];
          echo $result['type'];
         *
         */
        /*
          $result = Teacher::getTeacherName('non-executive');
         
          foreach($result as $key=>$one_teacher)
          {
                echo $key."<br>";
                echo $one_teacher['accname']."<br>";
                echo $one_teacher['type']."<br>";
                echo $one_teacher['fullname']."<br><br>";
          }
         * 
         */
/*
        $result = Teacher::getTeacherInfo('other_normal');
         
          foreach($result as $key=>$one_teacher)
          {
                echo $key."<br>";
                echo $one_teacher['type']."<br>";
                echo $one_teacher['fullname']."<br><br>";
          }
 * 
 */

        /*
          $result = Teacher::getIndividualTeacherDetail("178984");
          print_r($result);
         *
         */
/*
$result = TimetableDB::getReliefTimetable("", "", "2013-02-06");
foreach($result as $key=>$value)
{
    echo 'start : '.$key.' :<br><br>';
    
    foreach($value as $teaching)
    {
        echo $teaching['subject'].'<br>';
        echo $teaching['teacher-accname'].'<br>';
        echo $teaching['teacher-fullname'].'<br>';
        echo $teaching['relief-teacher-accname'].'<br>';
        echo $teaching['relief-teacher-fullname'].'<br>';
        echo $teaching['venue'].'<br>';
        print_r($teaching['class']);
        echo '<br>';
        echo '<br>';
    }
}
 * 
 */

//print_r(ListGenerator::getTeacherType());
//print_r(ListGenerator::getClassName('2013-02-06'));

//$result = ListGenerator::getTeacherName('2013-02-06', 0);
//print_r($result);
/*
foreach($result as $key => $value)
{
    echo $key.'<br>';
    echo $value.'<br>';
    echo '<br>';
}
 * 
 */

        //$result1 = Teacher::add('', 'temp', Array('fullname' => 'Robot Haphati Fckek', 'remark' => 'I am new here', 'datetime-from' => '2013-01-15 08:00', 'datetime-to' => '2013-01-16 08:00',  'email' => '111@adb.com', 'MT' => 'tamil'));
        //$result2 = Teacher::add('TMP1232312', 'temp', Array('remark' => 'I am new here', 'datetime-from' => '2013-01-15 08:00', 'datetime-to' => '2013-01-16 08:00', 'handphone' =>  '11111111'));
        //$result3 = Teacher::add('1692161' , 'leave',Array('datetime-from' => '2013-01-15 08:00', 'datetime-to' => '2013-01-16 08:00', 'remark' => 'okay haha'));
        //echo $result2;
        //
        //echo Teacher::delete(Array(2, 3,), 'leave');
        //Teacher::edit(1, "leave", Array('reason'=>'Ha ha', 'remark'=>'cdddc','datetime-from'=>'2013-02-11 11:15', 'datetime-to'=>'2013-02-11 14:15'));
        //Teacher::edit(1, "temp", Array('remark'=>'Hello world','datetime-from'=>'2013-01-14 08:15', 'datetime-to'=>'2013-01-14 12:15', 'email'=>'af@adf.com', 'handphone'=>'74787874', 'MT'=>'Chinese'));
        //********xue : testing end
/*
$result = Teacher::overallReport('net', '', SORT_DESC);
foreach($result as $value)
{
    print_r($value);
    echo "<br>";
}
 * 
 */
/*
$value = Teacher::individualReport('8104329');

    echo $value['numOfMC']."<br>";
    echo $value['numOfRelief']."<br>";
    print_r($value['mc']);
    echo "<br>";
    print_r($value['relief']);
    echo "<br>";
 * 
 */

//echo SchedulerDB::scheduleResultNum();
/*
$result = TimetableDB::getIndividualTimetable('2013-02-06', "7524281", -1);
foreach($result as $key=>$value)
{
    echo $key."<br>";
    echo $value['subject']."<br>";
    echo ($value['isRelief']?'YES':'NO')."<br>";
    echo $value['venue']." hehe<br>";
    print_r($value['class']);
    echo "<br><br>";
}
 * 
 */

//echo TimetableDB::checkTimetableConflict(0, Array(10, 11), "TMP5555555", "2013/2/06", "N111310111JC10");
/*
$result = SchedulerDB::getScheduleResult(0);

foreach($result as $a => $b)
{
    echo $a." : <br>";
    echo "-----------<br>";
    foreach($b as $c)
    {
        echo $c['teacherOnLeave']."<br>";
        echo $c['teacherAccName']."<br>";
        echo $c['reliefTeacher']."<br>";
        echo $c['reliefAccName']."<br>";
        print_r($c['time']);
        echo "<br>";
        print_r($c['class']);
        echo "<br><br>";
    }
}
 * 
 */

//echo print_r(SchedulerDB::allSchduleIndex());

/*
$result = TimetableDB::timetableForSem("8104329");
foreach($result as $key=>$row)
{
    echo "weekday : ".$key."<br>";
    echo "-------------<br>";
    foreach($row as $time=>$value)
    {
        echo "start : ".$time."<br>";
        echo "subject : ".$value['subject']."<br>";
        echo "venue : ".$value['venue']."<br>";
        echo "period : ".$value['period']."<br>";
        print_r($value['class']);
        echo "<br><br>";
    }
}
 * 
 */

//echo SchedulerDB::override(0, "N131310111JC10", "6644942", "TMP1111111")?"YES":"NO";
/*
$result = Teacher::getTeacherContact();
foreach($result as $key => $row)
{
    echo $key." : ";
    print_r($row);
    echo "<br>";
}
 * 
 */

//SchedulerDB::approve(2, '2013-02-06');
?>
