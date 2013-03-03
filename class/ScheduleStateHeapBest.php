<?php

class ScheduleStateHeapBest
{
    public $numberRequired;
    public $numberStates;
    public $heap;
    public $thresholdCost;
    public $thresholdNoLesson;
    public $isEnough;

    public function __construct($numberRequired)
    {
        $this->heap = array();
        $this->numberRequired = $numberRequired;
        $this->numberStates = 0;
        $this->isEnough = FALSE;
        $this->thresholdCost = NULL;
    }

    public function insert($aState)
    {
        /* @var $aState ScheduleState */
        /* @var $this->heap ScheduleStateHeapSimple */
        if ((empty($this->thresholdCost)) ||
                (($aState->expectedTotalCost < $this->thresholdCost)
                && ($aState->noLessons <= $this->thresholdNoLesson)))
        {
            $this->heap = array();
            $this->thresholdCost = $aState->expectedTotalCost;
            $this->thresholdNoLesson = $aState->noLessons;
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
        if (empty($this->thresholdCost))
        {
            return FALSE;
        } else
        {
            if ($score > $this->thresholdCost)
            {
                return TRUE;
            } else if (($score == $this->thresholdCost) && ($this->isEnough))
            {
                return TRUE;
            }
            return FALSE;
        }
    }
}
?>
