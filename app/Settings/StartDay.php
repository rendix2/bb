<?php

namespace App\Settings;

use DateInterval;
use Nette\Utils\DateTime;

/**
 * Description of StartDay
 *
 * @author rendix2
 */
class StartDay extends Setting
{
    
    /**
     *
     * @return DateInterval
     */
    public function getDiff()
    {
        $start = new DateTime($this->get());
        $end   = new DateTime();
                
        return $end->diff($start);
    }
    /**
     *
     */
    public function getRunningDays()
    {
        return $this->getDiff()->days;
    }
}
