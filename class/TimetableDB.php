<?php
require_once 'util.php';
require_once 'Teacher.php';
require_once 'DBException.php';

class TimetableDB
{
    
    /**
     * this function insert lesson_list into database
     * @param type $lesson_list
     * @param type $teacher_list
     * @param type $year  e.g. '12' representing 2012,  string
     * @param type $sem   '1' or '2'
     * @return Array : array of error message strings. Each of output[0] - output[6] represents a type of error. if, e.g. empty(output[1]), then there is no error type 1. echo output[0]~output[6] to see the error details. pay special attention of output[6], which represents abbre name not found error.
     */
    public static function insertTimetable($lesson_list, $teacher_list, $year='13', $sem='1')
    {
        //teacher list
        Teacher::getTeachersAccnameAndFullname($teacher_list);
        
        //sql statement construction
        $sql_insert_lesson = "insert into ct_lesson (lesson_id, weekday, start_time, end_time, subj_code, venue, type, highlighted) values ";
        $sql_insert_lesson_class = "insert into ct_class_matching values ";
        $sql_insert_lesson_teacher = "insert into ct_teacher_matching values ";
        
        $has_teacher = false;
        
        //a unique identifier for lesson
        //why i dont use $key: what if the $key is string not int
        //why i dont use auto increment : need to insert one record in one loop, not as efficient as aggregate insert
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
            
            if(empty($subject))
            {
                throw new DBException('Lesson '.$key." does not have subject", __FILE__, __LINE__);
            }
            if(empty($day_index) || !is_numeric($day_index) || $day_index < 1 || $day_index > Constant::num_of_week_day)
            {
                throw new DBException('Lesson '.$key."'s day index is not a number", __FILE__, __LINE__);
            }
            if(empty($start_time_index) || !is_numeric($start_time_index))
            {
                throw new DBException('Lesson '.$key."'s start time index is not a number", __FILE__, __LINE__);
            }
            if(empty($end_time_index) || !is_numeric($end_time_index))
            {
                throw new DBException('Lesson '.$key."'s end time index is not a number", __FILE__, __LINE__);
            }
            
            $lesson_id = TimetableDB::generateLessonPK('N', $year, $sem, $day_index, $start_time_index, $end_time_index, empty($value->classes)?array():array_keys($value->classes), empty($value->teachers)?array():array_keys($value->teachers));
            
            $sql_insert_lesson .= "('".mysql_real_escape_string($lesson_id)."', ".$day_index.", ".$start_time_index.", ".$end_time_index.", '".mysql_real_escape_string($subject)."', '".mysql_real_escape_string($venue)."', 'N', true), ";
            
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
        
        /*
        echo $sql_insert_lesson.'<br><br>';
        echo $sql_insert_lesson_class.'<br><br>';
        echo $sql_insert_lesson_teacher.'<br><br>';
         * 
         */
        
        //DB operation
        $db_url = Constant::db_url;
        $db_username = Constant::db_username;
        $db_password = Constant::db_password;
        $db_name = Constant::db_name;
        
        $db_con = mysql_connect($db_url, $db_username, $db_password);
        
        if (!$db_con)
        {
            throw new DBException("Fail to connect to database", __FILE__, __LINE__);
        }
        
        mysql_select_db($db_name, $db_con);
        
        //clear existing data
        $delete_sql_lesson = "delete from ct_lesson where type = 'N';";
        if (!mysql_query($delete_sql_lesson, $db_con))
        {
            throw new DBException("Fail to clear old data", __FILE__, __LINE__);
        }
        
        if(!mysql_query($sql_insert_lesson))
        {
            throw new DBException("Fail to insert into ct_lesson", __FILE__, __LINE__);
        }
        if(!mysql_query($sql_insert_lesson_class))
        {
            throw new DBException("Fail to insert into ct_class_matching", __FILE__, __LINE__);
        }
        if($has_teacher)
        {
            if(!mysql_query($sql_insert_lesson_teacher))
            {
                throw new DBException("Fail to insert into ct_teacher_matching", __FILE__, __LINE__);
            }
        }
        
        return true;
    }
    
    /**
     * time index is 0-based
     * @param type $accname - accname of leave teacher or ""
     * @param type $class - standard class name or ""
     * @param string $date "yyyy-mm-dd"
     * @param $scheduleIndex -1 : return confirmed; >=0, alternatives, $date is ignored
     * @return Complex data structure if succeed. null if fail.
     */
    public static function getReliefTimetable($accname, $class, $date, $scheduleIndex = -1)
    {
        $normal_dict = Teacher::getAllTeachers();
        $temp_dict = Teacher::getTempTeacher("");

        $db_con = Constant::connect_to_db('ntu');

        if(empty($db_con))
        {
            throw new DBException('Fail to query relief timetable', __FILE__, __LINE__);
        }
        
        if($scheduleIndex === -1)
        {
            if(empty($accname) && empty($class))
            {
                $sql_query_relief = "SELECT * FROM ((ct_lesson LEFT JOIN rs_relief_info ON ct_lesson.lesson_id = rs_relief_info.lesson_id) LEFT JOIN ct_class_matching ON ct_lesson.lesson_id = ct_class_matching.lesson_id) WHERE rs_relief_info.date = DATE('".mysql_real_escape_string(trim($date))."');";
            }
            else if(empty($accname) && !empty($class))
            {
                $sql_query_relief = "SELECT * FROM ((ct_lesson LEFT JOIN rs_relief_info ON ct_lesson.lesson_id = rs_relief_info.lesson_id) LEFT JOIN ct_class_matching ON ct_lesson.lesson_id = ct_class_matching.lesson_id) WHERE rs_relief_info.date = DATE('".mysql_real_escape_string(trim($date))."') and ct_class_matching.class_name = '".mysql_real_escape_string(trim($class))."';";
            }
            else if(!empty($accname) && empty($class))
            {
                $sql_query_relief = "SELECT * FROM ((ct_lesson LEFT JOIN rs_relief_info ON ct_lesson.lesson_id = rs_relief_info.lesson_id) LEFT JOIN ct_class_matching ON ct_lesson.lesson_id = ct_class_matching.lesson_id) WHERE rs_relief_info.date = DATE('".mysql_real_escape_string(trim($date))."') and rs_relief_info.leave_teacher = '".mysql_real_escape_string(trim($accname))."';";
            }
            else
            {
                $sql_query_relief = "SELECT * FROM ((ct_lesson LEFT JOIN rs_relief_info ON ct_lesson.lesson_id = rs_relief_info.lesson_id) LEFT JOIN ct_class_matching ON ct_lesson.lesson_id = ct_class_matching.lesson_id) WHERE rs_relief_info.date = DATE('".mysql_real_escape_string(trim($date))."') and ct_class_matching.class_name = '".mysql_real_escape_string(trim($class))."' and rs_relief_info.leave_teacher = '".mysql_real_escape_string(trim($accname))."';";
            }
        }
        else
        {
            if(empty($accname) && empty($class))
            {
                $sql_query_relief = "SELECT *, temp_each_alternative.start_time as start_time_index, temp_each_alternative.end_time as end_time_index FROM ((ct_lesson LEFT JOIN temp_each_alternative ON ct_lesson.lesson_id = temp_each_alternative.lesson_id) LEFT JOIN ct_class_matching ON ct_lesson.lesson_id = ct_class_matching.lesson_id) WHERE temp_each_alternative.schedule_id = ".$scheduleIndex.";";
            }
            else if(empty($accname) && !empty($class))
            {
                $sql_query_relief = "SELECT *, temp_each_alternative.start_time as start_time_index, temp_each_alternative.end_time as end_time_index FROM ((ct_lesson LEFT JOIN temp_each_alternative ON ct_lesson.lesson_id = temp_each_alternative.lesson_id) LEFT JOIN ct_class_matching ON ct_lesson.lesson_id = ct_class_matching.lesson_id) WHERE temp_each_alternative.schedule_id = ".$scheduleIndex." and ct_class_matching.class_name = '".mysql_real_escape_string(trim($class))."';";
            }
            else if(!empty($accname) && empty($class))
            {
                $sql_query_relief = "SELECT *, temp_each_alternative.start_time as start_time_index, temp_each_alternative.end_time as end_time_index FROM ((ct_lesson LEFT JOIN temp_each_alternative ON ct_lesson.lesson_id = temp_each_alternative.lesson_id) LEFT JOIN ct_class_matching ON ct_lesson.lesson_id = ct_class_matching.lesson_id) WHERE temp_each_alternative.schedule_id = ".$scheduleIndex." and temp_each_alternative.leave_teacher = '".mysql_real_escape_string(trim($accname))."';";
            }
            else
            {
                $sql_query_relief = "SELECT *, temp_each_alternative.start_time as start_time_index, temp_each_alternative.end_time as end_time_index FROM ((ct_lesson LEFT JOIN temp_each_alternative ON ct_lesson.lesson_id = temp_each_alternative.lesson_id) LEFT JOIN ct_class_matching ON ct_lesson.lesson_id = ct_class_matching.lesson_id) WHERE temp_each_alternative.schedule_id = ".$scheduleIndex." and ct_class_matching.class_name = '".mysql_real_escape_string(trim($class))."' and temp_each_alternative.leave_teacher = '".mysql_real_escape_string(trim($accname))."';";
            }
        }
        
        $query_relief_result = mysql_query($sql_query_relief);
        if(!$query_relief_result)
        {
            throw new DBException('Fail to query relief timetable', __FILE__, __LINE__);
        }
        
        $result = Array();

        while($row = mysql_fetch_assoc($query_relief_result))
        {
            $start_time_index = $row['start_time_index']-1;
            $end_time_index = $row['end_time_index']-1;

            for($i = $start_time_index; $i<$end_time_index; $i++)
            {
                if(empty($result[$i]))
                {
                    $result[$i] = Array();
                }

                $leave_teacher_id = $row['leave_teacher'];
                $relief_teacher_id = $row['relief_teacher'];
                $subject = $row['subj_code'];
                $venue = empty($row['venue'])?"":$row['venue'];

                $existed = false;

                for($j=0; $j<count($result[$i]);$j++)
                {
                    if(empty($result[$i][$j]))
                    {
                        continue;
                    }

                    if(strcmp($result[$i][$j]['subject'], $subject)===0 && strcmp($result[$i][$j]['teacher-accname'], $leave_teacher_id)===0 && strcmp($result[$i][$j]['relief-teacher-accname'], $relief_teacher_id)===0 && strcmp($result[$i][$j]['venue'], $venue)===0)
                    {
                        if(!empty($row['class_name']))
                        {
                            $existed = true;
                            $result[$i][$j]['class'][] = $row['class_name'];
                            break;
                        }
                    }
                }

                if(!$existed)
                {
                    if(array_key_exists($leave_teacher_id, $normal_dict))
                    {
                        $leave_name = $normal_dict[$leave_teacher_id]['name'];
                    }
                    else
                    {
                        $leave_name = "";
                    }

                    if(array_key_exists($relief_teacher_id, $temp_dict))
                    {
                        $relief_name = $temp_dict[$relief_teacher_id]['fullname'];
                    }
                    else if(array_key_exists($relief_teacher_id, $normal_dict))
                    {
                        $relief_name = $normal_dict[$relief_teacher_id]['name'];
                    }
                    else
                    {
                        $relief_name = "";
                    }

                    $new_teaching = Array(
                        'subject' => $subject,
                        'venue' => $venue,
                        'teacher-accname' => $leave_teacher_id,
                        'teacher-fullname' => $leave_name,
                        'relief-teacher-accname' => $relief_teacher_id,
                        'relief-teacher-fullname' => $relief_name,
                        'class' => Array(),
                        'id' => $row['lesson_id']
                    );

                    if(!empty($row['class_name']))
                    {
                        $new_teaching['class'][] = $row['class_name'];
                    }

                    $result[$i][] = $new_teaching;
                }
            }
        }

        return $result;
    }
    
    public static function uploadAEDTimetable($timetable, $year='13', $sem='1')
    {
        //sql statement construction
        $sql_insert_lesson = "insert into ct_lesson (lesson_id, weekday, start_time, end_time, subj_code, venue, type, highlighted) values ";
        $sql_insert_lesson_class = "insert into ct_class_matching values ";
        $sql_insert_lesson_teacher = "insert into ct_teacher_matching values ";
        $sql_delete_lesson = "delete from ct_lesson where lesson_id in (select distinct lesson_id from ct_teacher_matching where teacher_id in (";
        
        $delete_array = Array();
        
        foreach($timetable as $a_table)
        {
//            if(!is_int($a_table['day']) || !is_int($a_table['time-from']) || !is_int($a_table['time-to']))
//            {
//                return false;
//            }
            
            $venue = empty($a_table["venue"])?"":mysql_real_escape_string(trim($a_table["venue"]));
            $highlighted = $a_table['isHighlighted']?"true":"false";
            
            $lesson_id = TimetableDB::generateLessonPK('A', $year, $sem, $a_table['day'], $a_table['time-from'], $a_table['time-to'], $a_table['class'], Array($a_table['accname']));
            $sql_insert_lesson .= "('".mysql_real_escape_string($lesson_id)."', ".$a_table['day'].", ".$a_table['time-from'].", ".$a_table['time-to'].", '".mysql_real_escape_string(trim($a_table['subject']))."', '".$venue."', 'A', ".$highlighted."), ";
            
            //teacher
            $sql_insert_lesson_teacher .= "('".mysql_real_escape_string(trim($a_table['accname']))."', '".mysql_real_escape_string($lesson_id)."'), ";
            
            //class
            foreach($a_table['class'] as $class_name)
            {
                $sql_insert_lesson_class .= "('".mysql_real_escape_string($lesson_id)."', '".mysql_real_escape_string(trim($class_name))."'), ";
            }
            
            //delete
            $delete_array[] = $a_table['accname'];
        }
        
        foreach($delete_array as $a_delete)
        {
            $sql_delete_lesson .= "'".$a_delete."', ";
        }
        
        $sql_insert_lesson = substr($sql_insert_lesson, 0, -2).';';
        $sql_insert_lesson_class = substr($sql_insert_lesson_class, 0, -2).';';
        $sql_insert_lesson_teacher = substr($sql_insert_lesson_teacher, 0, -2).';';
        $sql_delete_lesson = substr($sql_delete_lesson, 0, -2).'));';
        
        $db_con = Constant::connect_to_db("ntu");
        if(empty($db_con))
        {
            return false;
        }
        
        if (!mysql_query($sql_delete_lesson, $db_con))
        {
            return false;
        }
        
        if(!mysql_query($sql_insert_lesson))
        {
            return false;
        }
        if(!mysql_query($sql_insert_lesson_class))
        {
            return false;
        }
        if(!mysql_query($sql_insert_lesson_teacher))
        {
            return false;
        }
        
        return true;
    }
    
    /**
     * 
     * @param string $date yyyy-mm-dd ignored if $scheduleIndex = -1
     * @param type $accname
     * @param int $$scheduleIndex 
     * @param type $isPreview
     * @return 
     */
    public static function getIndividualTimetable($date, $accname,$scheduleIndex = -1)
    {
        $result = Array();
        
        $db_con = Constant::connect_to_db('ntu');
        if(empty($db_con))
        {
            throw new DBException('Fail to get individual timetable', __FILE__, __LINE__);
        }
        
        //from timetable
        $date_obj = new DateTime($date);
        $weekday = $date_obj->format('N');

        $sql_query_timetable = "SELECT * FROM ((ct_lesson LEFT JOIN ct_class_matching ON ct_lesson.lesson_id = ct_class_matching.lesson_id) LEFT JOIN ct_teacher_matching ON ct_lesson.lesson_id = ct_teacher_matching.lesson_id) WHERE ct_lesson.weekday = ".$weekday." AND ct_teacher_matching.teacher_id = '".mysql_real_escape_string(trim($accname))."';";
        $query_timetable_result = Constant::sql_execute($db_con, $sql_query_timetable);
        if(is_null($query_timetable_result))
        {
            throw new DBException('Fail to get individual timetable', __FILE__, __LINE__);
        }

        foreach($query_timetable_result as $row)
        {
            $start_time = $row['start_time'] - 1;
            $end_time = $row['end_time'] - 1;

            for($i = $start_time; $i<$end_time; $i++)
            {
                if(array_key_exists($i, $result))
                {
                    if(strcmp($result[$i]['id'], $row['lesson_id']) === 0)
                    {
                        if(!empty($row['class_name']))
                        {
                            $result[$i]['class'][] = $row['class_name'];
                        }
                    }
                    else
                    {
                        throw new DBException('Overlap lesson at the same time for teacher '.$accname, __FILE__, __LINE__);
                    }
                }
                else
                {
                    $venue = empty($row['venue'])?"":$row['venue'];

                    $a_lesson = Array(
                        "id" => $row['lesson_id'],
                        "subject" => $row['subj_code'],
                        "venue" => $venue,
                        "isRelief" => false,
                        "class" => Array()
                    );
                    
                    if(!empty($row['class_name']))
                    {
                        $a_lesson['class'][] = $row['class_name'];
                    }

                    $result[$i] = $a_lesson;
                }
            }
        }
        
        //from relief timetable
        if($scheduleIndex === -1)
        {
            //confirmed
            $sql_query_relief = "select * from ((rs_relief_info left join ct_lesson on ct_lesson.lesson_id = rs_relief_info.lesson_id) left join ct_class_matching on ct_lesson.lesson_id = ct_class_matching.lesson_id) where rs_relief_info.date = DATE('".$date."') AND rs_relief_info.relief_teacher = '".$accname."';";
            $query_relief_result = Constant::sql_execute($db_con, $sql_query_relief);
            if(is_null($query_relief_result))
            {
                throw new DBException('Fail to query relief timetable for teacher '.$accname, __FILE__, __LINE__);
            }
            
            foreach($query_relief_result as $row)
            {
                $start_time = $row['start_time_index'] - 1;
                $end_time = $row['end_time_index'] - 1;

                for($i = $start_time; $i<$end_time; $i++)
                {
                    if(array_key_exists($i, $result))
                    {
                        if(strcmp($result[$i]['id'], $row['lesson_id']) === 0)
                        {
                            if(!empty($row['class_name']))
                            {
                                $result[$i]['class'][] = $row['class_name'];
                            }
                        }
                        else
                        {
                            throw new DBException('Overlap lesson at the same time for teacher '.$accname, __FILE__, __LINE__);
                        }
                    }
                    else
                    {
                        $venue = empty($row['venue'])?"":$row['venue'];

                        $a_lesson = Array(
                            "id" => $row['lesson_id'],
                            "subject" => $row['subj_code'],
                            "venue" => $venue,
                            "isRelief" => true,
                            "class" => Array()
                        );

                        if(!empty($row['class_name']))
                        {
                            $a_lesson['class'][] = $row['class_name'];
                        }

                        $result[$i] = $a_lesson;
                    }
                }
            }
        }
        else
        {
            //not confirmed
            $sql_query_relief = "select *, temp_each_alternative.start_time as start_time_index, temp_each_alternative.end_time as end_time_index from ((temp_each_alternative left join ct_lesson on ct_lesson.lesson_id = temp_each_alternative.lesson_id) left join ct_class_matching on ct_lesson.lesson_id = ct_class_matching.lesson_id) where temp_each_alternative.schedule_id = ".$scheduleIndex." AND temp_each_alternative.relief_teacher = '".$accname."';";
            $query_relief_result = Constant::sql_execute($db_con, $sql_query_relief);
            if(is_null($query_relief_result))
            {
                throw new DBException('Fail to query relief timetable for teacher '.$accname, __FILE__, __LINE__);
            }
            
            foreach($query_relief_result as $row)
            {
                $start_time = $row['start_time_index'] - 1;
                $end_time = $row['end_time_index'] - 1;

                for($i = $start_time; $i<$end_time; $i++)
                {
                    if(array_key_exists($i, $result))
                    {
                        if(strcmp($result[$i]['id'], $row['lesson_id']) === 0)
                        {
                            if(!empty($row['class_name']))
                            {
                                $result[$i]['class'][] = $row['class_name'];
                            }
                        }
                        else
                        {
                            throw new DBException('Overlap lesson at the same time for teacher '.$accname, __FILE__, __LINE__);
                        }
                    }
                    else
                    {
                        $venue = empty($row['venue'])?"":$row['venue'];

                        $a_lesson = Array(
                            "id" => $row['lesson_id'],
                            "subject" => $row['subj_code'],
                            "venue" => $venue,
                            "isRelief" => true,
                            "class" => Array()
                        );

                        if(!empty($row['class_name']))
                        {
                            $a_lesson['class'][] = $row['class_name'];
                        }

                        $result[$i] = $a_lesson;
                    }
                }
            }
        }
        
        return $result;
    }
    
    public static function checkTimetableConflict($schedule_index, $time_range, $accname, $schedule_date, $lesson_id)
    {
        $date_obj = new DateTime($schedule_date);
        $weekday = $date_obj->format('N');
        
        $db_con = Constant::connect_to_db('ntu');
        if(empty($db_con))
        {
            return -1;
        }
        
        $sql_normal = "select * from ct_lesson, ct_teacher_matching where ct_lesson.lesson_id = ct_teacher_matching.lesson_id and ct_teacher_matching.teacher_id = '".mysql_real_escape_string(trim($accname))."' and ct_lesson.weekday = ".$weekday." and highlighted and ((ct_lesson.start_time < ".$time_range[1].") and (ct_lesson.end_time > ".$time_range[0]."));";
        $normal_result = Constant::sql_execute($db_con, $sql_normal);
        if(is_null($normal_result))
        {
            return -1;
        }
        else if(count($normal_result) > 0)
        {
            return 1;
        }
        
        $sql_relief = "select * from rs_relief_info where relief_teacher = '".mysql_real_escape_string(trim($accname))."' and date = DATE('".mysql_real_escape_string(trim($schedule_date))."') and ((start_time_index < ".$time_range[1].") and (end_time_index > ".$time_range[0]."));";
        $relief_result = Constant::sql_execute($db_con, $sql_relief);
        if(is_null($relief_result))
        {
            return -1;
        }
        else if(count($relief_result) > 0)
        {
            return 1;
        }
        
        $sql_temp = "select * from temp_each_alternative where relief_teacher = '".mysql_real_escape_string(trim($accname))."' and date = DATE('".mysql_real_escape_string(trim($schedule_date))."') and schedule_id =".$schedule_index." and ((start_time < ".$time_range[1].") and (end_time > ".$time_range[0].")) and lesson_id != '$lesson_id';";
        $temp_result = Constant::sql_execute($db_con, $sql_temp);
        
        if(is_null($temp_result))
        {
            return -1;
        }
        else if(count($temp_result) > 0)
        {
            return 1;
        }
        
        return 0;
    }
    
    //0-based
    public static function timetableForSem($accname)
    {
        $db_con = Constant::connect_to_db('ntu');
        if(empty($db_con))
        {
            throw new DBException('Fail to query timetable for accname '.$accname, __FILE__, __LINE__);
        }
        
        $accname = mysql_real_escape_string(trim($accname));
        $sql_table = "select * from ((ct_lesson left join ct_class_matching on ct_lesson.lesson_id = ct_class_matching.lesson_id) left join ct_teacher_matching on ct_lesson.lesson_id = ct_teacher_matching.lesson_id) where ct_teacher_matching.teacher_id ='".$accname."';";
        $table_result = Constant::sql_execute($db_con, $sql_table);
        if(is_null($table_result))
        {
            throw new DBException('Fail to query timetable for accname '.$accname, __FILE__, __LINE__);
        }
        
        $result = Array();
        
        foreach($table_result as $row)
        {
            $day_index = $row['weekday'] - 1;
            
            if(!array_key_exists($day_index, $result))
            {
                $result[$day_index] = Array();
            }
            
            $start_time = $row['start_time'] - 1;
            
            if(!is_null($result[$day_index][$start_time]))
            {
                if(!empty($row['class_name']))
                {
                    $result[$day_index][$start_time]['class'][] = $row['class_name'];
                }
            }
            else
            {
                $period = $row['end_time'] - $row['start_time'];
                $subject = empty($row['subj_code'])?"":$row['subj_code'];
                $venue = empty($row['venue'])?"":$row['venue'];
                
                $result[$day_index][$start_time] = Array(
                    "class" => Array(),
                    "subject" => $subject,
                    "venue" => $venue,
                    "period" => $period
                );
                
                if(!empty($row['class_name']))
                {
                    $result[$day_index][$start_time]['class'][] = $row['class_name'];
                }
            }
        }
        
        return $result;
    }
    
    private static function generateLessonPK($type, $year, $sem, $weekday, $start_time, $end_time, $class_list, $teacher_list)
    {
        if(count($class_list) === 0)
        {
            $class_short = 'emp';
        }
        else
        {
            if(count($class_list) > 1)
            {
                sort($class_list);
            }
            
            $first_class = $class_list[0];
            
            $break_class_name = explode(" ", $first_class);
            
            if(count($break_class_name) === 1)
            {
                $class_short = $break_class_name[0];
            }
            else
            {
                if(strlen($break_class_name[0]) > 1)
                {
                    $class_short = substr($break_class_name[0], 0, 2).substr($break_class_name[1], 0, 1);
                }
                else if(strlen($break_class_name[1]) > 1)
                {
                    $class_short = $break_class_name[0].substr($break_class_name[1], 0, 2);
                }
                else
                {
                    $class_short = $break_class_name[0].$break_class_name[1];
                }
            }
        }
        
        if(count($teacher_list) === 0)
        {
            $teacher_short = 'emp';
        }
        else
        {
            if(count($teacher_list) > 1)
            {
                sort($teacher_list);
            }
            
            $first_teacher = $teacher_list[0];
            
            $break_teacher_name = explode(" ", $first_teacher);
            
            if(count($break_teacher_name) === 1)
            {
                if(strlen($break_teacher_name[0])>=3)
                {
                    $teacher_short = substr($break_teacher_name[0], 0, 3);
                }
                else
                {
                    $teacher_short = $break_teacher_name[0].rand(0, 99);
                }
                
            }
            else
            {
                if(strlen($break_teacher_name[0]) > 1)
                {
                    $teacher_short = substr($break_teacher_name[0], 0, 2).substr($break_teacher_name[1], 0, 1);
                }
                else if(strlen($break_teacher_name[1]) > 1)
                {
                    $teacher_short = $break_teacher_name[0].substr($break_teacher_name[1], 0, 2);
                }
                else
                {
                    $teacher_short = $break_teacher_name[0].$break_teacher_name[1];
                }
            }
        }
        
        return $type.$year.$sem.$weekday.$start_time.$end_time.$class_short.$teacher_short;
    }
    
    /**
     * given a date/year sem, this function returns the timetable ID
     * @param $mode : 0: by date; 1: by year and sem
     * @param array $para if mode=0, 'date'; if mode = 1, 'year'/'sem'
     * @return int >=0 : timetable index; -1, time out of range
     */
    public static function checkTimetableExistence($mode, $para)
    {
        $db_con = Constant::connect_to_db('ntu');
        if(empty($db_con))
        {
            return -1;
        }
        
        if($mode === 0)
        {
            $date = $para['date'];
            $sql_timetable_id = "select sem_id from ct_semester_info where DATE($date) between start_date and end_date;";
        }
        else if($mode === 1)
        {
            $year = $para['year'];
            $sem = $para['sem'];
            $sql_timetable_id = "select sem_id from ct_semester_info where year = '$year' and sem = $sem;";
        }
        else
        {
            return -1;
        }
        
        $query_result = Constant::sql_execute($db_con, $sql_timetable_id);
        if(is_null($query_result))
        {
            return -1;
        }
        if(count($query_result) === 0)
        {
            return -1;
        }
        
        return $query_result[0]['sem_id'] - 0;
    }
}
?>
