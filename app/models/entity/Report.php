<?php

namespace App\Models\Entity;

/**
 * Description of Report
 *
 * @author rendi
 */
class Report
{
    public $report_id;
    
    public $report_user_id;
    
    public $report_forum_id;
    
    public $report_topic_id;
    
    public $report_post_id;
    
    public $report_reported_user_id;     
    
    public $report_pm_id;
    
    public $report_text;
    
    public $report_time;
    
    public $report_status;

    public function __construct(
        $report_id,
        $report_user_id,    
        $report_forum_id,
        $report_topic_id,
        $report_post_id,
        $report_reported_user_id,      
        $report_pm_id,
        $report_text,    
        $report_time,    
        $report_status
    ) {
        $this->report_id               = $report_id === null ? null : (int)$report_id;
        $this->report_user_id          = $report_user_id === null ? null : (int)$report_user_id;
        $this->report_forum_id         = $report_forum_id === null ? null : (int)$report_forum_id;
        $this->report_topic_id         = $report_topic_id === null ? null : (int)$report_topic_id;
        $this->report_post_id          = $report_post_id === null ? null : (int)$report_post_id;
        $this->report_reported_user_id = $report_reported_user_id === null ? null : (int)$report_reported_user_id;
        $this->report_pm_id            = $report_pm_id === null ? null : (int)$report_pm_id;
        $this->report_text             = $report_text === null ? null : (int)$report_text;
        $this->report_time             = $report_time === null ? null : (int)$report_time;
        $this->report_status           = $report_status === null ? null : (int)$report_status;
    }
    
    /**
     * 
     * @param \Dibi\Row $values
     * 
     * @return \App\Models\Entity\Report
     */
    public static function get(\Dibi\Row $values)
    {
        return new Report(
            $values->report_id,
            $values->report_user_id,
            $values->report_forum_id,
            $values->report_topic_id,
            $values->report_post_id,
            $values->report_reported_user_id, 
            $values->report_pm_id,
            $values->report_text,
            $values->report_time,
            $values->report_status
        );
    }     
    
    /**
     * 
     * @return array
     */
    public function getArray()
    {
        return [   
            'report_id'               => $this->report_id,
            'report_user_id'          => $this->report_user_id,
            'report_forum_id'         => $this->report_forum_id,            
            'report_topic_id'         => $this->report_topic_id,
            'report_post_id'          => $this->report_post_id,
            'report_reported_user_id' => $this->report_reported_user_id,
            'report_pm_id'            => $this->report_pm_id, 
            'report_text'             => $this->report_text, 
            'report_time'             => $this->report_time, 
            'report_status'           => $this->report_status, 
        ];
    }
    
    /**
     * 
     * @return \Nette\Utils\ArrayHash
     */
    public function getArrayHash()
    {
        return \Nette\Utils\ArrayHash::from($this->getArray());
    }  
}
