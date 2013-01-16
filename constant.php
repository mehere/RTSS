<?php
class PageConstant
{
    const PRODUCT_NAME="iScheduler";
    const SCH_NAME_ABBR="CHIJ";
    const SCH_NAME="CHIJ St Nicholas Girl's School";
    
    const NUM_OF_YEAR=5; // number of year before & after current year in 'timetable/upload.php'
    
    // School start/end time
    public static $SCHOOL_START_TIME, $SCHOOL_END_TIME;
    const SCHOOL_TIME_INTERVAL=30; // minute
    
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
    public static function formatOptionInSelect($optionArr, $selectedOption)
    {        
        $output="";
        foreach ($optionArr as $key => $value)
        {
            $optionSelectedStr="";
            if (strcasecmp($selectedOption, $key) == 0) $optionSelectedStr='selected="selected"';
            $output .= <<< EOD
                <option value="$key" $optionSelectedStr>$value</option>
EOD;
        }
        return $output;
    }
}
PageConstant::$SCHOOL_START_TIME=mktime(7, 15);
PageConstant::$SCHOOL_END_TIME=mktime(14, 15);

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
                'accname'
            )
        ),
        
        'tempTeacher' => array(
            'display' => array(
                'fullname' => 'Name', 'handphone' => 'Handphone', 'datetime' => 'Time', 'remark' => 'Remark'
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
                'MC' => 'MC', 'maternity' => 'Maternity', 'holiday' => 'Holiday'
            ),
            'hidden' => array()
        )
    );
    
    // For /RTSS/relief/teacher-edit.php
    public static $RELIEF_EDIT=array(
        'teacherOnLeave' => array(
            'display' => array(
                'fullname' => 'Name', 'reason' => 'Reason', 'time' => 'Time', 'remark' => 'Remark'
            ),
            'hidden' => array(
                'accname'
            )
        ),
        
        'tempTeacher' => array(
            'display' => array(
                'fullname' => 'Name', 'handphone' => 'Phone', 'time' => 'Time Available', 'remark' => 'Remark'
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
        'class' => array(
            'display' => array(
                'time' => 'Time', 'teacher' => 'Teacher'
            ),
            'hidden' => array(
                'class'
            )
        ),
        
        'teacher' => array(
            'display' => array(
                'time' => 'Time', 'class' => 'Class'
            ),
            'hidden' => array(
                'teacher'
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
