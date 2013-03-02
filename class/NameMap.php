<?php
class NameMap
{
    // For /RTSS/relief/index.php
    public static $RELIEF=array(
        'teacherOnLeave' => array(
            'display' => array(
//                'fullname' => 'Name', 'type' => 'Type', 'datetime' => 'Time', 'reason' => 'Reason',
//                'teacherVerified' => 'Verified', 'teacherScheduled' => 'Scheduled'
                'fullname' => 'Name', 'type' => 'Type', 'datetime' => 'Time', 'handphone' => 'Handphone', 'reason' => 'Reason',
                'teacherScheduled' => '&#x267B;'
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

        'excludingList' => array(
            'display' => array(
                'non-executive' => 'Others', 'executive' => 'HOD/ExCo'
            ),
            'hidden' => array()
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
        ),

        'MT' => array(
            'display' => array(
                'en' => 'English', 'zh' => 'Chinese', 'ms' => 'Malay', 'ta' => 'Tamil'
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
                'fullname' => 'Name', 'handphone' => 'Contact', 'email' => 'Contact', 'MT' => 'MT',
                'datetime' => 'Time Available', 'remark' => 'Remark'
            ),
            'hidden' => array(
                'accname', 'leaveID'
            ),
            'saveKey' => array('datetime-from', 'datetime-to', 'handphone', 'email', 'MT', 'remark', 'accname'),
            'addKey' => array('fullname')
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
    public static $SMS=array(
        'layout' => array(
            'display' => array(
                'sentTime' => 'Sent',  'fullname' => 'Name',  'phone' => 'Phone', 'status' => 'Status',
                'repliedTime' => 'Replied',  'repliedMsg' => 'Message Replied'
            ),
            'hidden' => array(
                'smsID'
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
                'teacher-accname', 'relief-teacher-accname'
            )
        ),

        'individual' => array(
            'display' => array(
                'time' => 'Time',  'subject' => 'Subject',  'class' => 'Class', 'venue' => 'Venue'
            ),
            'hidden' => array(
                'teacher-accname', 'isRelief'
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
                'fullname' => 'Name', 'type' => 'Type', 'numOfMC' => 'MC', 'numOfRelief' => 'Relief', 'net' => 'Net'
            ),
            'hidden' => array(
                'accname'
            )
        ),

        'individual' => array(
            'display' => array(
                'numOfMC' => 'MC(times)', 'numOfRelief' => 'Relief(times)', 'net' => 'Net',
                'mc' => 'MC Period', 'relief' => 'Relief Period'
            ),
            'hidden' => array(
                'accname'
            )
        ),

        'teacherType' => array(
            'display' => array(
                'normal' => 'Normal', 'AED' => 'AED', 'untrained' => 'Untrained',
                'temp' => 'Temp', 'executive' => 'HOD/ExCo'
            ),
            'hidden' => array()
        )
    );
}
?>
