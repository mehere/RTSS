<?php

class ScheduleStateHeapBest
{
    public $numberRequired;
    public $numberStates;
    public $heap;
    public $threshold;
    public $isEnough;

    public function __construct($numberRequired)
    {
        $this->numberRequired = $numberRequired;
        $this->numberStates = 0;
        $this->isEnough = FALSE;
        $this->threshold = NULL;
    }

    public function insert($aState)
    {
        /* @var $aState ScheduleState */
        /* @var $this->heap ScheduleStateHeapSimple */
        if ((empty($this->threshold)) ||
                ($aState->expectedTotalCost < $this->threshold))
        {
            $this->heap = array();
            $this->threshold = $aState->expectedTotalCost;
            $this->numberStates = 1;
        } else
        {
            $this->numberStates++;
        }
        $this->heap[] = $aState;
        $this->isEnough = ($this->numberStates == $this->numberRequired);
    }

    public function isRejected($aState)
    {
        /* @var $aState ScheduleState*/
        $score = $aState->expectedTotalCost;
        if (empty($this->threshold))
        {
            return FALSE;
        } else
        {
            if ($score > $this->threshold)
            {
                return TRUE;
            } else if (($score == $this->threshold) && ($this->isEnough))
            {
                return TRUE;
            }
            return FALSE;
        }
    }
}
?>
