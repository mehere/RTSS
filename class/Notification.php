<?php
spl_autoload_register(function($class){
    require_once "$class.php";
});

Class Notification
{
    /**
     * This function is called after normal/ad hoc algo run.
     * @param int $schedule_index
     * @param array $$teacher_contact a dictionary of all teachers' contact
     * @param array $$date today, not date of scheduling
     */
    public static function sendReliefNotification($schedule_index, $old_relief_ids, $old_skip_ids, $teacher_contact, $date)
    {
        $sessionId = session_id();
        
        $db_con = Constant::connect_to_db('ntu');
        if(is_null($db_con))
        {
            throw new DBException('Fail to send notification', __FILE__, __LINE__);
        }
        
        //list : list of relief/skip record /unique_relief_key=>{...}
        /*
         * {accname => {
         *                  "relief" => {
         *                                  reliedf_id => {... 
         *                                                }
         *                              }
         *                  "skip" => {
         *                                  skip_id => {...  
         *                                              }
         *                              }
         *                  "old_relief" => ...
         *                  "old_skip" => ...
         *              }
         *  }
         */
        
        //1. query new relief
        $sql_selected = "select temp_relief_id, temp_each_alternative.lesson_id, temp_each_alternative.start_time_index, temp_each_alternative.end_time_index, relief_teacher, subj_code, venue, class_name from ((temp_each_alternative left join ct_lesson on temp_each_alternative.lesson_id = ct_lesson.lesson_id) left join ct_class_matching on ct_lesson.lesson_id = ct_class_matching.lesson_id) where schedule_id = $schedule_index;";
        $selected_result = Constant::sql_execute($db_con, $sql_selected);
        if (is_null($selected_result))
        {
            throw new DBException('Fail to send notification', __FILE__, __LINE__);
        }

        $list = array(); // for construct msg content
        
        foreach ($selected_result as $row)
        {
            $accname = $row['relief_teacher'];
            $relief_id = $row['temp_relief_id'];

            if (!array_key_exists($accname, $list))
            {
                $list[$accname] = array(
                    "relief" => array(),
                    "skip" => array(),
                    "old_relief" => array(),
                    "old_skip" => array()
                );
            }

            if (array_key_exists($relief_id, $list[$accname]["relief"]))
            {
                if (!empty($row['class_name']))
                {
                    $list[$accname]["relief"][$relief_id]['class'][] = $row['class_name'];
                }
            } 
            else
            {
                $venue = empty($row['venue']) ? "" : $row['venue'];
                $subject = empty($row['subj_code']) ? "" : $row['subj_code'];

                $one_relief = array(
                    "start_time" => $row['start_time_index'] - 0,
                    "end_time" => $row['end_time_index'] - 0,
                    "subject" => $subject,
                    "venue" => $venue,
                    "class" => array()
                );

                if (!empty($row['class_name']))
                {
                    $one_relief['class'][] = $row['class_name'];
                }

                $list[$accname]["relief"][$relief_id] = $one_relief;
            }
        }

        //2. query new skip
        $sql_selected_skip = "select temp_skip_id, temp_aed_skip_info.lesson_id, temp_aed_skip_info.start_time_index, temp_aed_skip_info.end_time_index, accname, subj_code, venue, class_name from ((temp_aed_skip_info left join ct_lesson on temp_aed_skip_info.lesson_id = ct_lesson.lesson_id) left join ct_class_matching on ct_lesson.lesson_id = ct_class_matching.lesson_id) where schedule_id = $schedule_index;";
        $selected_result_skip = Constant::sql_execute($db_con, $sql_selected_skip);
        if (is_null($selected_result_skip))
        {
            throw new DBException('Fail to send notification', __FILE__, __LINE__);
        }
        
        foreach ($selected_result_skip as $row)
        {
            $accname = $row['accname'];
            $skip_id = $row['temp_skip_id'];

            if (!array_key_exists($accname, $list))
            {
                $list[$accname] = array(
                    "relief" => array(),
                    "skip" => array(),
                    "old_relief" => array(),
                    "old_skip" => array()
                );
            }

            if (array_key_exists($skip_id, $list[$accname]["skip"]))
            {
                if (!empty($row['class_name']))
                {
                    $list[$accname]["skip"][$skip_id]['class'][] = $row['class_name'];
                }
            } 
            else
            {
                $venue = empty($row['venue']) ? "" : $row['venue'];
                $subject = empty($row['subj_code']) ? "" : $row['subj_code'];

                $one_skip = array(
                    "start_time" => $row['start_time_index'] - 0,
                    "end_time" => $row['end_time_index'] - 0,
                    "subject" => $subject,
                    "venue" => $venue,
                    "class" => array()
                );

                if (!empty($row['class_name']))
                {
                    $one_skip['class'][] = $row['class_name'];
                }

                $list[$accname]["skip"][$skip_id] = $one_skip;
            }
        }
        
        //query cancelled relief
        if(count($old_relief_ids) > 0)
        {
            $sql_selected = "select relief_id, rs_relief_info.lesson_id, rs_relief_info.start_time_index, rs_relief_info.end_time_index, relief_teacher, subj_code, venue, class_name from ((rs_relief_info left join ct_lesson on rs_relief_info.lesson_id = ct_lesson.lesson_id) left join ct_class_matching on ct_lesson.lesson_id = ct_class_matching.lesson_id) where relief_id in (".  implode(",", $old_relief_ids).");";
            $selected = Constant::sql_execute($db_con, $sql_selected);
            if(is_null($selected))
            {
                throw new DBException('Fail to send notification', __FILE__, __LINE__);
            }

            foreach ($selected as $row)
            {
                $accname = $row['relief_teacher'];
                $relief_id = $row['relief_id'];

                if (!array_key_exists($accname, $list))
                {
                    $list[$accname] = array(
                        "relief" => array(),
                        "skip" => array(),
                        "old_relief" => array(),
                        "old_skip" => array()
                    );
                }

                if (array_key_exists($relief_id, $list[$accname]["old_relief"]))
                {
                    if (!empty($row['class_name']))
                    {
                        $list[$accname]["old_relief"][$relief_id]['class'][] = $row['class_name'];
                    }
                } 
                else
                {
                    $venue = empty($row['venue']) ? "" : $row['venue'];
                    $subject = empty($row['subj_code']) ? "" : $row['subj_code'];

                    $one_relief = array(
                        "start_time" => $row['start_time_index'] - 0,
                        "end_time" => $row['end_time_index'] - 0,
                        "subject" => $subject,
                        "venue" => $venue,
                        "class" => array()
                    );

                    if (!empty($row['class_name']))
                    {
                        $one_relief['class'][] = $row['class_name'];
                    }

                    $list[$accname]["old_relief"][$relief_id] = $one_relief;
                }
            }
        }
        
        //4. query old skip
        if(count($old_skip_ids) > 0)
        {
            $sql_selected_skip = "select rs_aed_skip_info, rs_aed_skip_info.lesson_id, rs_aed_skip_info.start_time_index, rs_aed_skip_info.end_time_index, accname, subj_code, venue, class_name from ((rs_aed_skip_info left join ct_lesson on rs_aed_skip_info.lesson_id = ct_lesson.lesson_id) left join ct_class_matching on ct_lesson.lesson_id = ct_class_matching.lesson_id) where skip_id in (".  implode(",", $old_skip_ids).");";
            $selected_result_skip = Constant::sql_execute($db_con, $sql_selected_skip);
            if (is_null($selected_result_skip))
            {echo $sql_selected_skip;
                throw new DBException('Fail to send notification', __FILE__, __LINE__);
            }

            foreach ($selected_result_skip as $row)
            {
                $accname = $row['accname'];
                $skip_id = $row['skip_id'];

                if (!array_key_exists($accname, $list))
                {
                    $list[$accname] = array(
                        "relief" => array(),
                        "skip" => array(),
                        "old_relief" => array(),
                        "old_skip" => array()
                    );
                }

                if (array_key_exists($skip_id, $list[$accname]['old_skip']))
                {
                    if (!empty($row['class_name']))
                    {
                        $list[$accname]["old_skip"][$skip_id]['class'][] = $row['class_name'];
                    }
                } 
                else
                {
                    $venue = empty($row['venue']) ? "" : $row['venue'];
                    $subject = empty($row['subj_code']) ? "" : $row['subj_code'];

                    $one_skip = array(
                        "start_time" => $row['start_time_index'] - 0,
                        "end_time" => $row['end_time_index'] - 0,
                        "subject" => $subject,
                        "venue" => $venue,
                        "class" => array()
                    );

                    if (!empty($row['class_name']))
                    {
                        $one_skip['class'][] = $row['class_name'];
                    }

                    $list[$accname]["old_skip"][$skip_id] = $one_skip;
                }
            }
        }
        
        //5. construct sms
        $sms_input = Array();
        foreach ($list as $key => $one)
        {
            $accname = $key;

            if (!array_key_exists($accname, $teacher_contact))
            {
                $phone = "";
                $name = $accname;
            }
            else
            {
                $phone = $teacher_contact[$accname]['phone'];
                $name = $teacher_contact[$accname]['name'];
                
                if (empty($phone))
                {
                    $phone = "";
                }
                if (empty($name))
                {
                    $name = "Teacher";
                }
            }

            $message = "";

            /* wee : construct sms content using list array
            $index = 1;
            foreach ($one as $a_relief)
            {
                $start_time = SchoolTime::getTimeValue($a_relief['start_time']);
                $end_time = SchoolTime::getTimeValue($a_relief['end_time']);

                $classes = implode(",", $a_relief['class']);
                $subject = $a_relief['subject'];
                $venue = empty($a_relief['venue']) ? "in classroom" : $a_relief['venue'];

                $message .= "|    $index : On $date $start_time-$end_time take relief for $classes subject-$subject venue-$venue  |";

                $index++;
            }
             * 
             */

            $one_teacher = array(
                "phoneNum" => $phone,
                "name" => $name,
                "accName" => $accname,
                "message" => $message,
                "type" => 'R'
            );

            $sms_input[] = $one_teacher;
        }

        //4. send sms
        $all_input = array(
            "date" => $date,
            "input" => $sms_input
        );

        $_SESSION['sms']=$all_input;
        $absolute_path = dirname(__FILE__);
        BackgroundRunner::execInBackground(realpath($absolute_path.'\..\sms\sendSMS.php'), array('s'), array($sessionId));
        
        //5. construct
        $from = array(
            "email" => Constant::email,
            "password" => Constant::email_password,
            "name" => Constant::email_name,
            "smtp" => Constant::email_smtp,
            "port" => Constant::email_port,
            "encryption" => Constant::email_encryption
        );
        
        $to = array();
        foreach ($list as $key => $one)
        {
            $accname = $key;

            if (!array_key_exists($accname, $teacher_contact))
            {
                 $name = "";
                 $email = "";
            }
            else
            {
                $name = $teacher_contact[$accname]['name'];
                $email = $teacher_contact[$accname]['email'];

                if (empty($email))
                {
                    $email = "";
                }
                if (empty($name))
                {
                    $name = 'Teacher';
                }
            }

            $email_input = array();
            foreach ($one["relief"] as $a_relief)
            {
                $start_time = $a_relief['start_time'] - 1;
                $end_time = $a_relief['end_time'] - 1;

                for($i = $start_time; $i < $end_time; $i++)
                {
                    $subject = $a_relief['subject'];
                    $venue = empty($a_relief['venue']) ? "in classroom" : $a_relief['venue'];

                    $email_input[$i] = array(
                        "class" => $a_relief['class'],
                        "subject" => $subject,
                        "venue" => $venue
                    );
                }
            }

            //email format - to update
            $message = Email::formatEmail($name, $date, $email_input, Constant::email_name);

            $recepient = array(
                'accname' => $accname,
                'subject' => 'Relief timetable for today',
                'email' => $email,
                'message' => $message,
                'attachment' => "",
                'name' => $name
            );

            $to[] = $recepient;
        }

        $all_input_email = array(
            "from" => $from,
            "to" => $to
        );

        $_SESSION["email"] = $all_input_email;
        BackgroundRunner::execInBackground(realpath($absolute_path.'\..\sms\sendEmail.php'), array('s'), array($sessionId));
    }
    
    public static function sendCancelNotification($relief_ids, $skip_ids, $teacher_contact, $date)
    {
        $sessionId = session_id();
        
        $db_con = Constant::connect_to_db('ntu');
        if(empty($db_con))
        {
            throw new DBException('Fail to send notification', __FILE__, __LINE__);
        }
        
        //1. get relief to be cancelled
        if(count($relief_ids) > 0)
        {
            $sql_selected = "select relief_id, rs_relief_info.lesson_id, rs_relief_info.start_time_index, rs_relief_info.end_time_index, relief_teacher, subj_code, venue, class_name from ((rs_relief_info left join ct_lesson on rs_relief_info.lesson_id = ct_lesson.lesson_id) left join ct_class_matching on ct_lesson.lesson_id = ct_class_matching.lesson_id) where relief_id in (".  implode(",", $relief_ids).");";
            $selected = Constant::sql_execute($db_con, $sql_selected);
            if(is_null($selected))
            {
                throw new DBException('Fail to send notification', __FILE__, __LINE__);
            }

            //$list same as above
            $list = array(); // for construct msg content

            foreach ($selected as $row)
            {
                $accname = $row['relief_teacher'];
                $relief_id = $row['relief_id'];

                if (!array_key_exists($accname, $list))
                {
                    $list[$accname] = array(
                        "relief" => array(),
                        "skip" => array()
                    );
                }

                if (array_key_exists($relief_id, $list[$accname]["relief"]))
                {
                    if (!empty($row['class_name']))
                    {
                        $list[$accname]["relief"][$relief_id]['class'][] = $row['class_name'];
                    }
                } 
                else
                {
                    $venue = empty($row['venue']) ? "" : $row['venue'];
                    $subject = empty($row['subj_code']) ? "" : $row['subj_code'];

                    $one_relief = array(
                        "start_time" => $row['start_time_index'] - 0,
                        "end_time" => $row['end_time_index'] - 0,
                        "subject" => $subject,
                        "venue" => $venue,
                        "class" => array()
                    );

                    if (!empty($row['class_name']))
                    {
                        $one_relief['class'][] = $row['class_name'];
                    }

                    $list[$accname]["relief"][$relief_id] = $one_relief;
                }
            }
        }
        
        //2. query skip
        if(count($skip_ids) > 0)
        {
            $sql_selected_skip = "select rs_aed_skip_info, rs_aed_skip_info.lesson_id, rs_aed_skip_info.start_time_index, rs_aed_skip_info.end_time_index, accname, subj_code, venue, class_name from ((rs_aed_skip_info left join ct_lesson on rs_aed_skip_info.lesson_id = ct_lesson.lesson_id) left join ct_class_matching on ct_lesson.lesson_id = ct_class_matching.lesson_id) where skip_id in (".  implode(",", $skip_ids).");";
            $selected_result_skip = Constant::sql_execute($db_con, $sql_selected_skip);
            if (is_null($selected_result_skip))
            {echo $sql_selected_skip;
                throw new DBException('Fail to send notification', __FILE__, __LINE__);
            }

            foreach ($selected_result_skip as $row)
            {
                $accname = $row['accname'];
                $skip_id = $row['skip_id'];

                if (!array_key_exists($accname, $list))
                {
                    $list[$accname] = array(
                        "relief" => array(),
                        "skip" => array()
                    );
                }

                if (array_key_exists($skip_id, $list[$accname]['skip']))
                {
                    if (!empty($row['class_name']))
                    {
                        $list[$accname]["skip"][$skip_id]['class'][] = $row['class_name'];
                    }
                } 
                else
                {
                    $venue = empty($row['venue']) ? "" : $row['venue'];
                    $subject = empty($row['subj_code']) ? "" : $row['subj_code'];

                    $one_skip = array(
                        "start_time" => $row['start_time_index'] - 0,
                        "end_time" => $row['end_time_index'] - 0,
                        "subject" => $subject,
                        "venue" => $venue,
                        "class" => array()
                    );

                    if (!empty($row['class_name']))
                    {
                        $one_skip['class'][] = $row['class_name'];
                    }

                    $list[$accname]["skip"][$skip_id] = $one_skip;
                }
            }
        }
        
        //3. compose sms
        $sms_input = Array();
        foreach ($list as $key => $one)
        {
            $accname = $key;

            if (!array_key_exists($accname, $teacher_contact))
            {
                $phone = "";
                $name = $accname;
            }
            else
            {
                $phone = $teacher_contact[$accname]['phone'];
                $name = $teacher_contact[$accname]['name'];
                
                if (empty($phone))
                {
                    $phone = "";
                }
                if (empty($name))
                {
                    $name = "Teacher";
                }
            }

            $message = "";

            /* wee : construct sms content using list array
            
             * 
             */

            $one_teacher = array(
                "phoneNum" => $phone,
                "name" => $name,
                "accName" => $accname,
                "message" => $message,
                "type" => 'C'
            );

            $sms_input[] = $one_teacher;
        }
        
        //4. send sms
        $all_input = array(
            "date" => $date,
            "input" => $sms_input
        );

        $_SESSION['sms']=$all_input;
        $absolute_path = dirname(__FILE__);
        BackgroundRunner::execInBackground(realpath($absolute_path.'\..\sms\sendSMS.php'), array('s'), array($sessionId));
        
        //5. construct
        $from = array(
            "email" => Constant::email,
            "password" => Constant::email_password,
            "name" => Constant::email_name,
            "smtp" => Constant::email_smtp,
            "port" => Constant::email_port,
            "encryption" => Constant::email_encryption
        );
        
        $to = array();
        foreach ($list as $key => $one)
        {
            $accname = $key;

            if (!array_key_exists($accname, $teacher_contact))
            {
                 $name = "";
                 $email = "";
            }
            else
            {
                $name = $teacher_contact[$accname]['name'];
                $email = $teacher_contact[$accname]['email'];

                if (empty($email))
                {
                    $email = "";
                }
                if (empty($name))
                {
                    $name = 'Teacher';
                }
            }

            $email_input = array();
            foreach ($one["relief"] as $a_relief)
            {
                $start_time = $a_relief['start_time'] - 1;
                $end_time = $a_relief['end_time'] - 1;

                for($i = $start_time; $i < $end_time; $i++)
                {
                    $subject = $a_relief['subject'];
                    $venue = empty($a_relief['venue']) ? "in classroom" : $a_relief['venue'];

                    $email_input[$i] = array(
                        "class" => $a_relief['class'],
                        "subject" => $subject,
                        "venue" => $venue
                    );
                }
            }

            $message = Email::formatEmail($name, $date, $email_input, Constant::email_name);

            $recepient = array(
                'accname' => $accname,
                'subject' => 'Relief timetable for today',
                'email' => $email,
                'message' => $message,
                'attachment' => "",
                'name' => $name
            );

            $to[] = $recepient;
        }

        $all_input_email = array(
            "from" => $from,
            "to" => $to
        );

        $_SESSION["email"] = $all_input_email;
        BackgroundRunner::execInBackground(realpath($absolute_path.'\..\sms\sendEmail.php'), array('s'), array($sessionId));
    }
}
?>
