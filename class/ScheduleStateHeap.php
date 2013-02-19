<?php

class ScheduleStateHeap extends SplHeap
{
    public function compare(ScheduleState $state1, ScheduleState $state2)
    {
        $numDone1 = $state1->noLessons;
        $numDone2 = $state2->noLessons;
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

        return 0;
    }
}

?>
