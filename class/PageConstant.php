<?php
class PageConstant
{
    const PRODUCT_NAME="iScheduler";
    const SCH_NAME_ABBR="CHIJ";
    const SCH_NAME="CHIJ St Nicholas Girl's School";

    const DATE_FORMAT_ISO='Y/m/d';
    const DATE_FORMAT_SG='d/m/Y';
    const DATE_FORMAT_SG_DAY='l, d/m/Y';

    public static $DAY=array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday');

    public static $ERROR_TEXT=array(
        'login' => array(
            'mismatch' => 'Username or Password was entered incorrectly.'
        )
    );

    /**
     * Year range in timetable/admin.php
     * @param bool $showYearOnly will return sem no only
     * @return string print format of &lt;option&gt;
     */
    public static function printYearRange($showYearOnly=false)
    {
        $curYear=date('Y');

        if ($showYearOnly) return $curYear;

        $NUM_OF_YEAR=5; // number of year before & after current year in 'timetable/admin.php'

        $result=array();
        for ($i=$curYear-$NUM_OF_YEAR; $i<=$curYear+$NUM_OF_YEAR; $i++)
        {
            $result[]=$i;
        }

        return PageConstant::formatOptionInSelect($result, $curYear, TRUE);
    }

    /**
     * Sem range in timetable/admin.php
     * @param bool $showSemOnly will return sem no only
     * @return string print format of &lt;option&gt; (or return sem no)
     */
    public static function printSemRange($showSemOnly=false)
    {
        $curMonth=date('n');
        $SEM_DIVIDER=6.5;

        $result=array(1, 2);

        $curSem=$curMonth < $SEM_DIVIDER ? 1 : 2;
        if ($showSemOnly) return $curSem;

        return PageConstant::formatOptionInSelect($result, $curSem, TRUE);
    }

    /**
     * Output string representation of option array in &lt;select&gt; tag
     * @param array $optionArr {key}/{value} pair as in: &lt;option value=&quot;{key}&quot;&gt;{value}&lt;/option&gt;
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
     * Wrap an element inside 'td' tag
     * @param string $ele the element
     * @param string $style 'style' attribute i.e. style="color: red"
     * @return string HTML representation
     */
    public static function tdWrap($ele, $style='')
    {
//        $ele=htmlentities($ele);
        return "<td $style>$ele</td>";
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
            case 0: return "&#x2717;";
            case 1: return "&#x2713;";
        }
        return '';
    }

    /**
     * Calculate net value based on $numOfMC and $numOfRelief
     * @param int $numOfMC
     * @param int $numOfRelief
     * @return int net value
     */
    public static function calculateNet($numOfMC, $numOfRelief)
    {
        return $numOfMC-$numOfRelief;
    }
}
?>
