<?php
class PageConstant
{
    const PRODUCT_NAME="iScheduler";
    const SCH_NAME_ABBR="CHIJ";
    const SCH_NAME="CHIJ St Nicholas Girl's School";
    
    const NUM_OF_YEAR=5; // number of year before & after current year in 'timetable/admin.php'        
    
    public static $DAY=array('Monday', 'Tuesday', 'Wedsday', 'Thursday', 'Friday');
    
    public static $ERROR_TEXT=array(
        'login' => array(
            'mismatch' => 'Username or Password was entered incorrectly.',
            'loginFirst' => 'Please log in first.'
        )
    );
    
    /**
     * Output string representation of option array in 'select' tag
     * @param array $optionArr {key}/{value} pair as in: <option value="{key}">{value}</option>
     * @param string $selectedOption option is to be selected
     * @return string output 
     */
    public static function formatOptionInSelect($optionArr, $selectedOption, $useValueOnly=false)
    {        
        $output="";
        foreach ($optionArr as $key => $value)
        {
            $optionSelectedStr="";
            $optionKey=$useValueOnly?$value:$key;
            if (strcasecmp($selectedOption, $optionKey) == 0) $optionSelectedStr='selected="selected"';            
            $output .= <<< EOD
                <option value="$optionKey" $optionSelectedStr>$value</option>
EOD;
        }
        return $output;
    }
    
    /**
     * Escape HTML entity in each element of input array. Directly change on that array
     * @param array $arr input array
     */
    public static function escapeHTMLEntity(&$arr)
    {
        array_walk_recursive($arr, array('PageConstant', 'escape'));
    }
    
    // as in above function
    private static function escape(&$ele, $key)
    {
        $ele=htmlentities($ele);
    }
    
    /**
     * Output mark/sign based on input state
     * @param int $state 0, 1
     * @return string 0: No; 1: Yes 
     */
    public static function stateRepresent($state)
    {
        switch ($state)
        {
            case 0: return "&#x2717";
            case 1: return "&#x2713";
        }
        return '';
    }
}

class SchoolTime
{
    private static $SCHOOL_TIME_ARR=null; // interval -- minute
    
    public function __construct()
    {
        if (is_null(self::$SCHOOL_TIME_ARR)) 
        {            
            self::$SCHOOL_TIME_ARR=array(mktime(7, 25));
            
            $endTime=mktime(14,15);            
            for ($curTime=mktime(7, 45); $curTime<=$endTime; $curTime+=30*60)
            {
                self::$SCHOOL_TIME_ARR[]=$curTime;
            }
        }        
    }    
    
    private static function formatTime($time)
    {
        return date("H:i", $time);
    }
    
    /**
     * Get specific time value for an index
     * @param int $index
     * @return formatted string
     */
    public static function getTimeValue($index)
    {
        new SchoolTime;
        return self::formatTime(self::$SCHOOL_TIME_ARR[$index]);
    }
    
    /**
     * Get an array of time representation
     * @param int $start
     * @param int $end non-positive means counting from the end
     * @return an array of formatted string
     */
    public static function getTimeArrSub($start, $end)
    {
        new SchoolTime;
        if ($end <= 0)
        {
            $end=count(self::$SCHOOL_TIME_ARR)+$end-1;
        }
        return array_map(array('SchoolTime', 'formatTime'), array_slice(self::$SCHOOL_TIME_ARR, $start, $end-$start+1));
    }
}

//include_once 'class/teacher.php';
//var_dump(Teacher::add(7341327, 'leave', 'Bernard Wong Weng Keong', 'MC',
//                'sdf', '2013-01-27 07:25', '2013-01-27 14:15', '', '', ''));

class NameMap
{
    // For /RTSS/relief/index.php
    public static $RELIEF=array(
        'teacherOnLeave' => array(
            'display' => array(
                'fullname' => 'Name', 'type' => 'Type', 'datetime' => 'Time', 'reason' => 'Reason', 
                'teacherVerified' => 'Verified', 'teacherScheduled' => 'Scheduled'
            ),
            'hidden' => array(
                'accname', 'leaveID'
            )
        ),
        
        'tempTeacher' => array(
            'display' => array(
                'fullname' => 'Name', 'handphone' => 'Handphone', 'datetime' => 'Time Available', 'remark' => 'Remark'
            ),
            'hidden' => array(
                'accname'
            )
        ),
        
        'teacherDetail' => array(
            'display' => array(
                'accname' => 'Account', 'fullname' => 'Name', 'subject' => 'Subject',
                'email' => 'Email', 'handphone' => 'Handphone'
            ),
            'hidden' => array()
        ),
        
        'leaveReason' => array(
            'display' => array(
                'MC' => 'MC', 'pr-MC' => 'Pro-rated MC', 'hospitalization' => 'Hospitalization', 
                'maternity' => 'Maternity', 'child-care' => 'Child Care', 
                'official-VR' => 'Official VR', 'private-VR' => 'Private VR', 'pr-VR' => 'Pro-rated VR', 'wo-VR' => 'Without VR',
                'couse-seminar' => 'Course or Seminar', 'external-official-duty' => 'External Official Duty',
                'others' => 'Others'
            ),
            'hidden' => array()
        )
    );
    
    // For /RTSS/relief/teacher-edit.php
    public static $RELIEF_EDIT=array(
        'teacherOnLeave' => array(
            'display' => array(
                'fullname' => 'Name', 'reason' => 'Reason', 'datetime' => 'Time', 'remark' => 'Remark'
            ),
            'hidden' => array(
                'accname', 'leaveID'
            ),
            'saveKey' => array('datetime-from', 'datetime-to', 'reason', 'remark'),
            'addKey' => array('accname', 'fullname')
        ),
        
        'tempTeacher' => array(
            'display' => array(
                'fullname' => 'Name', 'handphone' => 'Phone', 'datetime' => 'Time Available', 'remark' => 'Remark'
            ),
            'hidden' => array(
                'accname'
            )
        )
    );
    
    // For /RTSS/relief/schedule/ & ~result.php
    public static $SCHEDULE_RESULT=array(
        'preview' => array(
            'display' => array(
                'type' => 'Type', 'minPeriod' => 'Min Period', 
                'MT' => 'Mother Togue'
            ),
            'hidden' => array()
        ),
        
        'schedule' => array(
            'display' => array(
                'class' => 'Class', 'time' => 'Time', 
                'teacherOnLeave' => 'Teacher on Leave', 'reliefTeacher' => 'Relief Teacher'
            ),
            'hidden' => array(
                'scheduleIndex'
            )
        )
    );
    
    // For /RTSS/relief/timetable/
    public static $TIMETABLE=array(
        'layout' => array(
            'display' => array(
                'time' => 'Time',  'subject' => 'Subject',  'class' => 'Class', 'venue' => 'Venue', 
                'teacher-fullname' => 'Teacher',  'relief-teacher-fullname' => 'Relief Teacher'
            ),
            'hidden' => array(
                'teacher-accname', 'relief-teacher-aaccname'
            )
        ),
        
        'namematch' => array(
            'display' => array(
                'abbrname' => 'Abbreviation', 'fullname' => 'Full Name'
            ),
            'hidden' => array(
                'accname'
            )
        )
    );
    
    // For /RTSS/relief/report/
    public static $REPORT=array(
        'overall' => array(
            'display' => array(
                'fullname' => 'Name', 'type' => 'Type', 'mc' => 'MC', 'relief' => 'Relief', 'net' => 'Net'                
            ),
            'hidden' => array(
                'accname'
            )
        ),
        
        'individual' => array(
            'display' => array(
                'mc' => 'MC Date', 'relief' => 'Relief Date'
            ),
            'hidden' => array(
                'accname'
            )
        )
    );
}
?>
