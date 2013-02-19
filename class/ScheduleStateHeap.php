<?php

class ScheduleStateHeap extends SplHeap
{
    public function compare(ScheduleState $state1, ScheduleState $state2)
    {
        $numDone1 = count($state1->lessonsNotAllocated);
        $numDone2 = count($state2->lessonsNotAllocated);
        if ($numDone1 != $numDone2)
        {
            return ($numDone1 < $numDone2) ? 1 : -1;
        }

        $expectedTotalCost1 = $state1->expectedTotalCost;
        $expectedTotalCost2 = $state2->expectedTotalCost;
        if ($expectedTotalCost1 != $expectedTotalCost2)
        {
            return ($expectedTotalCost1 < $expectedTotalCost2) ? 1 : -1;
        }

//        $factor1_fairnessCost1 = $state1->factor1_fairnessCost;
//        $factor1_fairnessCost2 = $state2->factor1_fairnessCost;
//        if ($factor1_fairnessCost1 != $factor1_fairnessCost2)
//        {
//            return ($factor1_fairnessCost1 > $factor1_fairnessCost2 ) ? 1 : -1;
//        }
//
//        $factor2_hassleCost1 = $state1->factor2_hassleCost;
//        $factor2_hassleCost2 = $state2->factor2_hassleCost;
//        if ($factor2_hassleCost1 != $factor2_hassleCost2)
//        {
//            return ($factor2_hassleCost1 < $factor2_hassleCost2) ? 1 : -1;
//        }

        return 0;
    }

}

?>
