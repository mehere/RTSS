<?php

require_once 'Teacher.php';
require_once 'TimetableAnalyzer.php';
require_once 'Scheduling.php';
require_once 'DayTime.php';
require_once 'Lesson.php';
require_once 'TimetableDB.php';
require_once 'ListGenerator.php';

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

//********xue : testing
//$scheduling = new Scheduling("2013-01-15");
/*
$result = $scheduling->getNormalLessonsToday();
echo $result['success']?"okay<br>":"no<br>";
echo $result['error_msg']."<br>";
foreach($result["teachers"] as $key=>$value)
{
    echo $key." : <br>";
    print_r($value->leave);
    echo "<br>";
    print_r($value->timetable);
    echo "<br>";
    echo $value->accname."<br>";
    echo $value->name."<br>";
    echo $value->noLessonMissed."<br>";
    echo $value->noLessonRelived."<br>";
    echo "<br>";
}
 * 
 */
/*
$result = $scheduling->getAEDLessonsToday();
echo $result['success']?"okay<br>":"no<br>";
echo $result['error_msg']."<br>";
foreach($result["teachers"] as $key=>$value)
{
    echo $key." : <br>";
    print_r($value->leave);
    echo "<br>";
    print_r($value->timetable);
    echo "<br>";
    echo $value->accname."<br>";
    echo $value->name."<br>";
    echo $value->noLessonMissed."<br>";
    echo $value->noLessonRelived."<br>";
    echo "<br>";
}
 * 
 */
/*
$result = $scheduling->getUntrainedTeachers();
echo $result['success']?"okay<br>":"no<br>";
echo $result['error_msg']."<br>";
foreach($result["teachers"] as $key=>$value)
{
    echo $key." : <br>";
    echo $value->accname."<br>";
    echo $value->name."<br>";
    echo $value->noLessonMissed."<br>";
    echo $value->noLessonRelived."<br>";
    echo "<br>";
}
 * 
 */
/*
$result = $scheduling->getTempTeachers();
echo $result['success']?"okay<br>":"no<br>";
echo $result['error_msg']."<br>";
foreach($result["teachers"] as $key=>$value)
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
//echo Teacher::calculateLeaveSlot("8104329", "2013-01-08 0915", "2013-01-08 1315");
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
          $arrTeachersNew=Teacher::getTeachersAccnameAndFullname($arrTeachers);
          foreach($arrTeachersNew as $a_teacher)
          {
          echo $a_teacher->abbreviation."<br>";
          echo $a_teacher->name."<br>";
          echo $a_teacher->accname."<br><br>";
          }
         */

        //Teacher::insertAbbrMatch(array('AF ADF'=>'2344244'));
        //Teacher::insertAbbrMatch(array('ADE'=>'122333121', 'ASDFASF'=>'434332333','AF ADF'=>'2344244','CDDE'=>'ASFEAF'));
        
        /*
          $query_date = "2013-01-15";
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
          $query_date = "2013-01-14";
          $temp_teacher = Teacher::getTempTeacher("");
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
          $result = Teacher::getTeacherName('');
         
          foreach($result as $key=>$one_teacher)
          {
                echo $key."<br>";
                echo $one_teacher['accname']."<br>";
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
$result = TimetableDB::getReliefTimetable("", "", "2013-01-14");
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
//print_r(ListGenerator::getClassName('2013-02-09'));
/*
$result = ListGenerator::getTeacherName('2013-02-08');
foreach($result as $value)
{
    echo $value['accname'].'<br>';
    echo $value['fullname'].'<br>';
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
        //Teacher::edit(3, "leave", Array('reason'=>'He he', 'remark'=>'cdc','datetime-from'=>'2013-02-14 08:15', 'datetime-to'=>'2013-02-14 12:15'));
        //Teacher::edit(1, "temp", Array('remark'=>'Hello world','datetime-from'=>'2013-01-14 08:15', 'datetime-to'=>'2013-01-14 12:15', 'email'=>'af@adf.com', 'handphone'=>'74787874', 'MT'=>'Chinese'));
        //********xue : testing end
?>
