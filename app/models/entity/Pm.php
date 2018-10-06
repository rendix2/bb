<?php

namespace App\Models\Entity;

/**
 * Description of Pm
 *
 * @author rendi
 */
class Pm
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
        return [
            'pm_id'            => $this->pm_id, 
            'pm_user_id_from'  => $this->pm_user_id_from,
            'pm_user_id_to'    => $this->pm_user_id_to, 
            'pm_subject'       => $this->pm_subject, 
            'pm_text'          => $this->pm_text,
            'pm_status'        => $this->pm_status,
            'pm_time_sent'     => $this->pm_time_sent,
            'pm_time_read'     => $this->pm_time_read
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
