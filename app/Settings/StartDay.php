<?php

namespace App\Settings;

/**
 * Description of StartDay
 *
 * @author rendi
 */
class StartDay
{
    private $startDay;
    
    public function __construct($startDay)
    {
        $this->startDay = $startDay;
        
        //\Tracy\Debugger::barDump($this->getDiff());
    }
    
    public function getStartDay()
    {
        return $this->startDay;
    }
    
    /**
     * 
     * @return \DateInterval
     */
    public function getDiff()
    {
        $start = new \Nette\Utils\DateTime($this->startDay);
        $end   = new \Nette\Utils\DateTime();
                
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
