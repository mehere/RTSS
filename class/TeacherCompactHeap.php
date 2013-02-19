<?php

class TeacherCompactHeap extends SplHeap
{
    function compare(TeacherCompact $teacher1, TeacherCompact $teacher2)
    {
        $type1 = $teacher1->getTypeNo();
        $type2 = $teacher2->getTypeNo();
        if ($type1 != $type2)
        {
            return ($type1 < $type2) ? 1 : -1;
        }

        // 1st sort: Teaching Periods
        $noTeachingPeriod1 = $teacher1->noTeachingPeriod;
        $noTeachingPeriod2 = $teacher2->noTeachingPeriod;
        if ($noTeachingPeriod1 != $noTeachingPeriod2)
        {
            return ($noTeachingPeriod1 < $noTeachingPeriod2) ? 1 : -1;
        }

        // 2nd sort: Done Before
        $hasDone1 = $teacher1->hasDone;
        $hasDone2 = $teacher2->hasDone;
        if ($hasDone1 != $hasDone2)
        {
            return ($hasDone1 == FALSE) ? 1 : -1;
        }

        // 3rd sort: Net Relief
        $netRelived1 = $teacher1->netRelived;
        $netRelived2 = $teacher2->netRelived;
        if ($netRelived1 != $netRelived2)
        {
            return ($netRelived1 < $netRelived2) ? 1 : -1;
        }

        return 0;
    }
}

?>
