<?php
class SchoolTime
{
    private static $SCHOOL_TIME_ARR=null; // interval -- minute
    
    private static $SEM_PERIOD=null;

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
            
            // Sem period
            self::$SEM_PERIOD=array(
                array(array(1, 1), array(6, 30)),
                array(array(7, 1), array(12, 31))
            );
        }
    }

    private static function formatTime($time)
    {
        return date("H:i", $time);
    }

    /**
     * Get specific time value for an index
     * @param int $index (starting from 1)
     * @return formatted string; if $index out of range, return null
     */
    public static function getTimeValue($index)
    {
        new SchoolTime;
        if ($index < 1 || $index > count(self::$SCHOOL_TIME_ARR)) return null;
        return self::formatTime(self::$SCHOOL_TIME_ARR[$index-1]);
    }

    /**
     * Get an index for a specific time value
     * @param string $timeValue if ($isTimeObject is true) then here a time obj is expected; otherwise, string of format H:m i.e. 07:25
     * @param bool $isTimeObject (false by default)
     * @return int index (starting from 1); -1 means not exist
     */
    public static function getTimeIndex($timeValue, $isTimeObject=false)
    {
        new SchoolTime;
        for ($i=0; $i<count(self::$SCHOOL_TIME_ARR); $i++)
        {
            $time=self::$SCHOOL_TIME_ARR[$i];
            if ($isTimeObject && $time == $timeValue || !$isTimeObject && self::formatTime($time) == $timeValue)
            {
                return $i+1;
            }
        }

        return -1;
    }

    /**
     * 
     * @param string $timeValue "hh:mm"
     * @param type $mode 0 - largest index smaller than or equal to input; temp disabled
     */
    public static function getApproTimeIndex($timeValue)
    {
        $times = explode(":", $timeValue);
        $hour = $times[0] - 0;
        $minute = $times[1] - 0;
        
        $appro_hour = 1 + ($hour - 7) * 2;
        
        if($appro_hour < 1)
        {
            return 1;
        }
        if($appro_hour > 15)
        {
            return 15;
        }
        
        if($appro_hour === 1 && $minute < 45)
        {
            return 1;
        }
        if($minute >= 15 && $minute < 45)
        {
            return $appro_hour;
        }
        else if($minute >=45 && $minute <= 59)
        {
            return $appro_hour + 1;
        }
        
        return $appro_hour - 1;
    }
    
    /**
     * Get an array of time representation
     * @param int $start
     * @param int $end negative means counting from the end
     * @return an array of formatted string
     */
    public static function getTimeArrSub($start, $end, $isAssociate=false)
    {
        new SchoolTime;
        if ($end < 0)
        {
            $end=count(self::$SCHOOL_TIME_ARR)+$end;
        }
        return array_map(array('SchoolTime', 'formatTime'), array_slice(self::$SCHOOL_TIME_ARR, $start, $end-$start+1, $isAssociate));
    }
    
    /**
     * 
     * @param int $year
     * @param int $semNo 1 or 2
     * @param int $formatOption 0 (default) -- ISO string, 1 -- obj
     * @return array [startDate, endDate]. Each element(obj or string based on $formatOption)
     *      if $semNo out of range, return null
     */
    public static function getSemPeriod($year, $semNo, $formatOption=0)
    {
        new SchoolTime;
        if ($semNo < 1 || $semNo > 2) return null;
        
        $period=SchoolTime::$SEM_PERIOD[$semNo-1];
        $period[0]="$year/{$period[0][0]}/{$period[0][1]}";
        $period[1]="$year/{$period[1][0]}/{$period[1][1]}";
        
        switch ($formatOption)
        {
            case 1: 
                $period[0]=new DateTime($period[0]);
                $period[1]=new DateTime($period[1]);
                break;
        }      
        
        return $period;
    }
    
    /**
     *      
     * @param int $option 0 -- sem, 1 -- year
     * @param DateTime $date use Y/m/d ( null -- current date )
     * @return string (-1 means out of range)
     */
    public static function getSemYearFromDate($option=0, $date=null)
    {
        new SchoolTime;
        
        $curDate=$date ? $date : new DateTime();
        $curYear=$curDate->format('Y');
        $tmpDate=clone $curDate;
        
        switch ($option)
        {
            case 1:
                return $curYear;
            default:
                $sem1=SchoolTime::$SEM_PERIOD[0];
                $sem2=SchoolTime::$SEM_PERIOD[1];

                if ($curDate >= $tmpDate->setDate($curYear, $sem1[0][0], $sem1[0][1]) &&
                        $curDate <= $tmpDate->setDate($curYear, $sem1[1][0], $sem1[1][1]))
                {
                    return 1;
                }
                
                if ($curDate >= $tmpDate->setDate($curYear, $sem2[0][0], $sem2[0][1]) &&
                        $curDate <= $tmpDate->setDate($curYear, $sem2[1][0], $sem2[1][1]))
                {
                    return 2;
                }
                
        }
        return -1;
    }

    /**
     * 
     * @param DateTime $date1
     * @param DateTime $date2
     * @return bool if they are within the same sem
     */
    public static function checkDatesInSameSem($date1, $date2)
    {
        new SchoolTime;
        
        return (SchoolTime::getSemYearFromDate(0, $date1) == SchoolTime::getSemYearFromDate(0, $date2))
                && (SchoolTime::getSemYearFromDate(1, $date1) == SchoolTime::getSemYearFromDate(1, $date2));        
    }
    

    /**
     *
     * @param type $dateString
     * @param type $formatOption 0 (default) -- from ISO to SG, 1 -- from SG to ISO, 2 -- from ISO to SG_DAY
     * @return type
     */
    public static function convertDate($dateString, $formatOption=0)
    {
        switch ($formatOption)
        {
            case 1:
                return date_format(DateTime::createFromFormat(PageConstant::DATE_FORMAT_SG, $dateString), PageConstant::DATE_FORMAT_ISO);
            case 2:
                return date_format(DateTime::createFromFormat(PageConstant::DATE_FORMAT_ISO, $dateString), PageConstant::DATE_FORMAT_SG_DAY);                
        }
        
        return date_format(DateTime::createFromFormat(PageConstant::DATE_FORMAT_ISO, $dateString), PageConstant::DATE_FORMAT_SG);            
    }

    /**
     *
     * @param type $dateObject
     * @param type $formatOption 0 (default) -- ISO, 1 -- SG
     * @return type
     */
    public static function displayDate($dateObject, $formatOption=0)
    {
        switch ($formatOption)
        {
            case 1:
                return date_format($dateObject, PageConstant::DATE_FORMAT_SG);
            default:
                return date_format($dateObject, PageConstant::DATE_FORMAT_ISO);
        }
    }
    
    /**
     * 1-based. the array key starts from 1
     * @param int $sem 1 or 2
     * @param string $year 4 digit string
     * @return array 
     */
    public static function getSchoolTimeList($sem, $year, $weekday = 1)
    {
        $db_con = Constant::connect_to_db('ntu');

        if (empty($db_con))
        {
            throw new DBException("Fail to query time", __FILE__, __LINE__);
        }
        
        $sql_query = "select * from ct_time_list where sem_id in (select distinct sem_id from ct_semester_info where year = '$year' and sem_num = $sem) and weekday = $weekday;";
        $query = Constant::sql_execute($db_con, $sql_query);
        
        if(is_null($query))
        {
            return array();
        }
        
        $result = array();
        
        foreach($query as $row)
        {
            $result[$row['time_index']] = $row['time_value'];
        }
        
        return $result;
    }
    
    /**
     * 
     * @param string $currentDate date string
     * @return array or null if date outside range
     */
    public static function checkSemInfo($currentDate)
    {
        $db_con = Constant::connect_to_db('ntu');

        if (empty($db_con))
        {
            throw new DBException("Fail to query sem info", __FILE__, __LINE__);
        }
        
        $clear_date = mysql_real_escape_string(trim($currentDate));
        $sql_sem = "select * from ct_semester_info where DATE('$clear_date') between DATE(start_date) and DATE(end_date);";
        $sem = Constant::sql_execute($db_con, $sql_sem);
        
        if(is_null($sem))
        {
            throw new DBException("Fail to query sem info", __FILE__, __LINE__);
        }
        if(count($sem) === 0)
        {
            return null;
        }
        $result = array(
            "startDate" => $sem[0]['start_date'],
            "endDate" => $sem[0]['end_date'],
            "year" => $sem[0]['year'],
            "sem" => $sem[0]['sem_num']
        );
        
        return $result;
    }
}
?>
