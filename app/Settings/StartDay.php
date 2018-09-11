<?php

namespace App\Settings;

use DateInterval;
use Nette\Utils\DateTime;

/**
 * Description of StartDay
 *
 * @author rendix2
 */
class StartDay
{
    private $startDay;

    /**
     * StartDay constructor.
     *
     * @param string $startDay
     */
    public function __construct($startDay)
    {
        $this->startDay = $startDay;
        
        //\Tracy\Debugger::barDump($this->getDiff());
    }

    /**
     * @return string
     */
    public function getStartDay()
    {
        return $this->startDay;
    }
    
    /**
     *
     * @return DateInterval
     */
    public function getDiff()
    {
        $start = new DateTime($this->startDay);
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
