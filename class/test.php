<?php
require_once 'email_lib/swift_required.php';
spl_autoload_register(function($class){
    require_once "$class.php";
});
session_start();
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
/*
Teacher::setExcludingList("2013/01/01", Array('123','2345'));
print_r(Teacher::getExcludingList("2013/01/01"));
 *
 */
/*
$lesson_1 = Array(
    "subject" => "Math",
    "venue" => "LT 2A",
    "accname" => "1234567",
    "time-from" => 1,
    "time-to" => 4,
    "day" => 1,
    "isHighlighted" => true,
    "class" => Array('1A', '2B', '3C')
);
$lesson_2 = Array(
    "subject" => "Eco",
   "venue" => null,
    "accname" => "2323123",
    "time-from" => 4,
    "time-to" => 6,
    "day" => 5,
    "isHighlighted" => false,
    "class" => Array('5A', '2B', '3C')
);
$input = Array($lesson_1, $lesson_2);
 *
 */
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
//$scheduling->cleanForAlgo();
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
    //echo "speciality : ".$value->speciality."<br>";
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
        //$timetableanalyzer->readCsv('normal_algo_testing_timetable_skip.csv');
       /*
        TimetableDB::insertTimetable($arrLessons, $arrTeachers);
        Test::insertAEDTimetable($arrLessons, $arrTeachers);
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
        //echo Teacher::insertAbbrMatch(array('ADE'=>'122333121', 'ASDFASF'=>'434332333','AF ADF'=>'2344244','CDDE'=>'ASFEAF'));

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

//$result = AdHocSchedulerDB::getReliefPlan('2013-02-06');
//$result = AdHocSchedulerDB::getSkippingPlan('2013-02-06');
/*
$index = 1;
foreach($result as $one)
{
    echo ($index++)."<br>";
    echo $one->lessonId."<br>";
    echo $one->startTimeSlot."<br>";
    echo $one->endTimeSlot."<br>";
    echo $one->teacherOriginal."<br>";
    echo $one->teacherRelief."<br>";
    echo "<br>";
}
 *
 */
/*
$result = AdHocSchedulerDB::getBlockingPlan('2013-02-06');
$index = 1;
foreach($result as $row)
{
    echo ($index++)."<br>";
    echo $row->teachers[0]."<br>";
    echo $row->startTimeSlot."<br>";
    echo $row->endTimeSlot."<br>";
    echo $row->subject."<br>";
    echo "<br>";
}
 *
 */
/*
$result = User::queryTeacherID("S8104329I", "Li Huili");
echo "id : ".$result;
 *
 */
        /*
          $result = User::login("G6471009K", '0707');
          echo "accname : ".$result['accname']."<br>";
          echo "type : ".$result['type']."<br>";
          echo "fullname : ".$result['fullname']."<br>";
         *
         */
        /*
          $result = Teacher::getTeacherName('');

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
        $result = Teacher::getTeacherInfo('temp');

          foreach($result as $key=>$one_teacher)
          {
                echo $key."<br>";
                echo $one_teacher['type']."<br>";
                echo $one_teacher['fullname']."<br><br>";
          }
 *
 */

        /*
          $result = Teacher::getIndividualTeacherDetail("TMP1111111");
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
/*
$result = ListGenerator::getTeacherName('2013/03/27', -1);
print_r($result);
 *
 */
/*
foreach($result as $key => $value)
{
    echo $key.'<br>';
    echo $value.'<br>';
    echo '<br>';
}
 *
 */
/*
$result = AdHocSchedulerDB::getApprovedSchedule('2013-02-06');
foreach($result as $key=>$value)
{
    echo $key." : <br>";
    echo $value['reliefTeacher']."<br>";

    $lessons = $value['lesson'];
    foreach($lessons as $id=>$one)
    {
        echo "**********<br>";
        echo $id." : <br>";
        echo $one['lessonID']."<br>";
        echo $one['reliefID']."<br>";
        print_r($one['time']);
        echo "<br>";
        print_r($one['class']);
        echo "<br>";
        echo "..........<br>";
    }

    echo "<br><br>";
}
 *
 */
        //$result1 = Teacher::add('', 'temp', Array('fullname' => 'Robot Haphati Fckek', 'remark' => 'I am new here', 'datetime-from' => '2013-01-15 08:00', 'datetime-to' => '2013-01-16 08:00',  'email' => '111@adb.com', 'MT' => 'tamil'));
        //$result2 = Teacher::add('TMP1111111', 'temp', Array('remark' => 'I am new here', 'datetime-from' => '2013-01-15 08:00', 'datetime-to' => '2013-01-16 08:00', 'handphone' =>  '11111111'));
        //$result3 = Teacher::add('7032095' , 'leave',Array('datetime-from' => '2013-02-06 08:15', 'datetime-to' => '2013-02-06 13:15', 'remark' => 'okay haha'));
        //echo $result3;
        //
        //echo Teacher::delete(Array(11, 12, 13), 'leave');
        //echo Teacher::delete(Array(2), 'leave');
        //Teacher::delete(array(31), "leave", true);
        //Teacher::edit(1, "leave", Array('reason'=>'Ha ha', 'remark'=>'cdddc','datetime-from'=>'2013-02-06 11:15'));//, 'datetime-to'=>'2013-02-11 14:15'
        //Teacher::edit(1, "temp", Array('remark'=>'Hello world','datetime-from'=>'2013-02-06 08:15', 'email'=>'dddddddf@adf.com', 'handphone'=>'23232323', 'MT'=>'Malay')); //'datetime-to'=>'2013-01-14 12:15',
        //********xue : testing end
/*
$result = Teacher::overallReport('', 'net', SORT_DESC, "2013", 1);
foreach($result as $value)
{
    print_r($value);
    echo "<br>";
}
 *
 */
/*
$value = Teacher::individualReport('8909732');

    echo $value['numOfMC']."<br>";
    echo $value['numOfRelief']."<br>";
    print_r($value['mc']);
    echo "<br>";
    print_r($value['relief']);
    echo "<br>";
 *
 */
//echo SchedulerDB::scheduleResultNum();

//$result = TimetableDB::getIndividualTimetable('2013-02-06', "6937933", 0);  //go through normal before approve
//$result = TimetableDB::getIndividualTimetable('2013-02-06', "7032095", -1);  //go through normal after approve, AED
//$result = TimetableDB::getIndividualTimetable('2013-02-06', "6937933", 0, "ad_hoc");  //go through ad hoc before approve
//$result = TimetableDB::getIndividualTimetable('2013-02-06', "6937933", -1, "ad_hoc");  //go through ad hoc after approve
/*
foreach($result as $key=>$value)
{
    echo $key."<br>";
    echo $value['subject']."<br>";
    echo $value['attr']."<br>";
    echo $value['venue']." hehe<br>";
    print_r($value['class']);
    echo "<br>";
    print_r($value['skipped']);

    echo "<br><br>";
}
 *
 */
/*
$result = TimetableDB::getCollectiveTimetable('2013-02-06', array("7032095", "6937933"), 0);
foreach($result as $acc => $subresult)
{
    echo $acc." : <br>";
    foreach($subresult as $key=>$value)
    {
        echo $key."<br>";
        echo $value['subject']."<br>";
        echo $value['attr']."<br>";
        echo $value['venue']." hehe<br>";
        print_r($value['class']);
        echo "<br>";
        print_r($value['skipped']);

        echo "<br><br>";
    }
}
 * 
 */
//echo TimetableDB::checkTimetableConflict(0, array(4, 5), "7576699", "2013/2/06", "N1313126HD65");
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
        echo $c['reliefID']."<br>";
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
$result = TimetableDB::timetableForSem("7032095");
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

//SchedulerDB::overrideSet('end', 0);

//var_dump(SchedulerDB::override(0,2705, '8800121')); //extreme case : override one AED with another AED
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

//SchedulerDB::approve(0, '2013-02-06');

//AdHocSchedulerDB::cancelRelief(821, 2, 5);

//print_r(AdHocSchedulerDB::adHocApprove(0, '2013-02-06'));

//AdminConfig::setRecommendedLesson(10);
//echo $scheduling->getRecommendedNoOfLessons();

/*
class Test
{
    public static function insertAEDTimetable($lesson_list, $teacher_list, $year='2013', $sem=1)
    {
        //insert semester info
        $sem_id = TimetableDB::checkTimetableExistence(1, array('year'=>$year, 'sem'=>$sem));

        if($sem_id === -1)
        {
            if($sem === 1)
            {
                $sem_start_date = "$year-".Constant::$sem_dates[0];
                $sem_end_date = "$year-".Constant::$sem_dates[1];
            }
            else if($sem === 2)
            {
                $sem_start_date = "$year-".Constant::$sem_dates[2];
                $sem_end_date = "$year-".Constant::$sem_dates[3];
            }
            else
            {
                throw new DBException('Wrong semester number', __FILE__, __LINE__, 3);
            }

            $db_con = Constant::connect_to_db('ntu');

            if (empty($db_con))
            {
                throw new DBException("Fail to connect to database", __FILE__, __LINE__);
            }

            $sql_insert_sem = "insert into ct_semester_info (start_date, end_date, year, sem_num) values ('$sem_start_date', '$sem_end_date', '$year', $sem);";
            $insert_sem = Constant::sql_execute($db_con, $sql_insert_sem);
            if(is_null($insert_sem))
            {
                throw new DBException("Fail to store semester information", __FILE__, __LINE__, 2);
            }

            $sem_id = mysql_insert_id();
        }

        //teacher list
        //temp - will delete later
        Teacher::getTeachersAccnameAndFullname($teacher_list);

        //sql statement construction
        $sql_insert_lesson = "insert into ct_lesson (lesson_id, weekday, start_time_index, end_time_index, subj_code, venue, type, highlighted, sem_id) values ";
        $sql_insert_lesson_class = "insert into ct_class_matching values ";
        $sql_insert_lesson_teacher = "insert into ct_teacher_matching values ";

        $has_teacher = false;
        $has_lesson = false;
        $has_class = false;

        foreach($lesson_list as $key=>$value){
            //insert into ct_lesson table
            $subject = $value->subject;
            $day_index = $value->day;
            $start_time_index = $value->startTimeSlot;
            $end_time_index = $value->endTimeSlot;
            $venue = "";
            if (!(empty($value->venue))){
                $venue = $value->venue;
            }

            if(empty($day_index) || !is_numeric($day_index) || $day_index < 1 || $day_index > Constant::num_of_week_day)
            {
                throw new DBException('Lesson '.$key."'s day index is not a number", __FILE__, __LINE__, 2);
            }
            if(empty($start_time_index) || !is_numeric($start_time_index))
            {
                throw new DBException('Lesson '.$key."'s start time index is not a number", __FILE__, __LINE__, 2);
            }
            if(empty($end_time_index) || !is_numeric($end_time_index))
            {
                throw new DBException('Lesson '.$key."'s end time index is not a number", __FILE__, __LINE__, 2);
            }

            $lesson_id = TimetableDB::generateLessonPK('A', $year, $sem, $day_index, $start_time_index, $end_time_index, empty($value->classes)?array():array_keys($value->classes), empty($value->teachers)?array():array_keys($value->teachers));

            $sql_insert_lesson .= "('".mysql_real_escape_string(trim($lesson_id))."', ".$day_index.", ".$start_time_index.", ".$end_time_index.", '".mysql_real_escape_string(trim($subject))."', '".mysql_real_escape_string(trim($venue))."', 'A', true, $sem_id), ";
            $has_lesson = true;

            //insert into ct_class_matching
            $classes = $value->classes;

            if(count($classes)>0)
            {
                foreach ($classes as $aClass) {
                    $class_name = $aClass->name;

                    if(empty($class_name))
                    {
                        throw new DBException('Lesson '.$key." has empty class name", __FILE__, __LINE__);
                    }

                    $sql_insert_lesson_class .= "('".mysql_real_escape_string($lesson_id)."', '".mysql_real_escape_string($class_name)."'), ";

                    $has_class = true;
                }
            }

            //insert into ct_teacher_matching
            $teachers = $value->teachers;

            foreach ($teachers as $a_teacher){
                $abbre_name = $a_teacher->abbreviation;
                $teacher_accname = $teacher_list[$abbre_name]->accname;

                if(empty($teacher_accname))
                {
                    //throw new DBException($abbre_name." does not have accname", __FILE__, __LINE__);
                    continue;
                }

                $sql_insert_lesson_teacher .= "('".mysql_real_escape_string($teacher_accname)."', '".mysql_real_escape_string($lesson_id)."'), ";

                $has_teacher = true;
            }
        }

        $sql_insert_lesson = substr($sql_insert_lesson, 0, -2).';';
        $sql_insert_lesson_class = substr($sql_insert_lesson_class, 0, -2).';';
        $sql_insert_lesson_teacher = substr($sql_insert_lesson_teacher, 0, -2).';';


        echo $sql_insert_lesson.'<br><br>';
        echo $sql_insert_lesson_class.'<br><br>';
        echo $sql_insert_lesson_teacher.'<br><br>';


        $db_con_new = Constant::connect_to_db('ntu');

        if (empty($db_con_new))
        {
            throw new DBException("Fail to connect to database", __FILE__, __LINE__);
        }

        //clear existing data
        $delete_sql_lesson = "delete from ct_lesson where type = 'A' and sem_id = $sem_id;";

        $clear_old_result = Constant::sql_execute($db_con_new, $delete_sql_lesson);
        if (is_null($clear_old_result))
        {
            throw new DBException("Fail to clear old data", __FILE__, __LINE__, 2);
        }

        //insert new data
        if($has_lesson)
        {
            $insert_lesson = Constant::sql_execute($db_con_new, $sql_insert_lesson);
            if(is_null($insert_lesson))
            {
                throw new DBException("Fail to insert into ct_lesson", __FILE__, __LINE__, 2);
            }
        }
        if($has_class)
        {
            $insert_class = Constant::sql_execute($db_con_new, $sql_insert_lesson_class);
            if(is_null($insert_class))
            {
                throw new DBException("Fail to insert into ct_class_matching", __FILE__, __LINE__, 2);
            }
        }
        if($has_teacher)
        {
            $insert_teacher = Constant::sql_execute($db_con_new, $sql_insert_lesson_teacher);
            if(is_null($insert_teacher))
            {
                throw new DBException("Fail to insert into ct_teacher_matching", __FILE__, __LINE__, 2);
            }
        }

        return true;
    }
}
 *
 */

$content=array('subject'=>'test', 'email'=>'ya0002ei@e.ntu.edu.sg', 
            'message'=>Email::formatEmail('$name', array('$date' => array(
                3 => 
array('id' =>'N1313356HL', 'subject' =>'EL', 'venue' =>'', 'class'=>array('A1', 'B1'), 'attr' =>'2',
    'skipped'=>array('class' => array('6H'), 'subject'=>'Cool')
),
                5 => 
array('id' =>'N1313356HLx', 'subject' =>'EL', 'venue' =>'KKK', 'class'=>array('A1', 'B1'), 'attr' =>'-1'    
),
                10 => 
array('id' =>'N1313356HLy', 'subject' =>'ELX', 'venue' =>'Ya', 'class'=>array('A1', 'B1'), 'attr' =>'1'    
)
            )), '$sender_name'));

//$content2=$content;
//$content2['email']='youngyw8@gmail.com';

//var_dump(Email::sendMail(array('email'=>Constant::email, 'password'=>Constant::email_password, 'name'=>Constant::email_name,
//        'smtp'=>Constant::email_smtp, 'port'=>Constant::email_port, 'encryption'=>Constant::email_encryption), 
//        array($content)));

?>
