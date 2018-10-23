<?php

namespace App\Models\Entity;

/**
 * Description of Report
 *
 * @author rendix2
 */
class Report extends \App\Models\Entity\Base\Entity
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

    /**
     * 
     * @param int $report_id
     * @param int $report_user_id
     * @param int $report_forum_id
     * @param int $report_topic_id
     * @param int $report_post_id
     * @param int $report_reported_user_id
     * @param int $report_pm_id
     * @param string $report_text
     * @param int $report_time
     * @param int $report_status
     */
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
        $this->report_text             = $report_text === null ? null : $report_text;
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
        $res = [];
        
        if (isset($this->report_id)) {
            $res['report_id'] = $this->report_id; 
        }

        if (isset($this->report_user_id)) {
            $res['report_user_id'] = $this->report_user_id; 
        }

        if (isset($this->report_forum_id)) {
            $res['report_forum_id'] = $this->report_forum_id; 
        }

        if (isset($this->report_topic_id)) {
            $res['report_topic_id'] = $this->report_topic_id; 
        }

        if (isset($this->report_post_id)) {
            $res['report_post_id'] = $this->report_post_id; 
        }
        
        if (isset($this->report_reported_user_id)) {
            $res['report_reported_user_id'] = $this->report_reported_user_id; 
        }
        
        if (isset($this->report_pm_id)) {
            $res['report_pm_id'] = $this->report_pm_id; 
        }

        if (isset($this->report_text)) {
            $res['report_text'] = $this->report_text; 
        }

        if (isset($this->report_time)) {
            $res['report_time'] = $this->report_time; 
        }

        if (isset($this->report_status)) {
            $res['report_status'] = $this->report_status; 
        }        

        return $res;
    }
}
