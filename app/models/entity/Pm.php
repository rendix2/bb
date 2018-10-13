<?php

namespace App\Models\Entity;

/**
 * Description of Pm
 *
 * @author rendi
 */
class Pm extends \App\Models\Entity\Base\Entity
{
    
    public $pm_id;
    
    public $pm_user_id_from;
    
    public $pm_user_id_to;
    
    public $pm_subject;
    
    public $pm_text;
    
    public $pm_status;
    
    public $pm_time_sent;
    
    public $pm_time_read;
    
    /**
     * 
     * @param int    $pm_id
     * @param int    $pm_user_id_from
     * @param int    $pm_user_id_to
     * @param string $pm_subject
     * @param string $pm_text
     * @param string $pm_status
     * @param int    $pm_time_sent
     * @param int    $pm_time_read
     */
    public function __construct(
        $pm_id,
        $pm_user_id_from,    
        $pm_user_id_to,
        $pm_subject,    
        $pm_text,    
        $pm_status,    
        $pm_time_sent,
        $pm_time_read
    ) {
        $this->pm_id           = $pm_id;
        $this->pm_user_id_from = $pm_user_id_from;
        $this->pm_user_id_to   = $pm_user_id_to;
        $this->pm_subject      = $pm_subject;
        $this->pm_text         = $pm_text;
        $this->pm_status       = $pm_status;
        $this->pm_time_sent    = $pm_time_sent;
        $this->pm_time_read    = $pm_time_read;
    }
    
    /**
     * 
     * @param \Dibi\Row $values
     * 
     * @return \App\Models\Entity\Pm
     */
    public function get(\Dibi\Row $values)
    {
        return new Pm(
            $values->pm_id,
            $values->pm_user_id_from,
            $values->pm_user_id_to,
            $values->pm_subject,
            $values->pm_text,
            $values->pm_status,
            $values->pm_time_sent,
            $values->pm_time_read                
        );
    }
    
    /**
     * 
     * @return array
     */
    public function getArray()
    {
        $res = [];
        
        if (isset($this->pm_id)) {
            $res['pm_id'] = $this->pm_id;
        }
        
        if (isset($this->pm_user_id_from)) {
            $res['pm_user_id_from'] = $this->pm_user_id_from;
        }

        if (isset($this->pm_user_id_to)) {
            $res['pm_user_id_to'] = $this->pm_user_id_to;
        }

        if (isset($this->pm_subject)) {
            $res['pm_subject'] = $this->pm_subject;
        }

        if (isset($this->pm_text)) {
            $res['pm_text'] = $this->pm_text;
        }

        if (isset($this->pm_status)) {
            $res['pm_status'] = $this->pm_status;
        }   
        
        if (isset($this->pm_time_sent)) {
            $res['pm_time_sent'] = $this->pm_time_sent;
        }      

        if (isset($this->pm_status)) {
            $res['pm_time_read'] = $this->pm_time_read;
        }              
        
        return $res;
    }
}
