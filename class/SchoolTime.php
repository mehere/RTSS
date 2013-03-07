<?php
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
                break;
            default:
                return date_format($dateObject, PageConstant::DATE_FORMAT_ISO);
        }
    }
}
?>
