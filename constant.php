<?php
class Constant
{
    const PRODUCT_NAME="iScheduler";
    const SCH_NAME_ABBR="CHIJ";
    const SCH_NAME="CHIJ St Nicholas Girl's School";
}

class NameMap
{
    // For /RTSS/relief/index.php
    public static $RELIEF=array(
        'teacherOnLeave' => array(
            'display' => array(
                'fullname' => 'Name', 'type' => 'Type', 'reason' => 'Reason', 
                'teacherVerified' => 'Verified', 'teacherScheduled' => 'Scheduled'
            ),
            'hidden' => array(
                'accname'
            )
        ),
        
        'tempTeacher' => array(
            'display' => array(
                'fullname' => 'Name', 'handphone' => 'Handphone', 'time' => 'Time', 'remark' => 'Remark'
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
                'fullname' => 'Name', 'handphone' => 'Handphone', 'time' => 'Time Available', 'remark' => 'Remark'
            ),
            'hidden' => array(
                'accname'
            )
        )
    );
}
?>
