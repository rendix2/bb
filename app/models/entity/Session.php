<?php

namespace App\Models\Entity;

/**
 * Description of Session
 *
 * @author rendi
 */
class Session extends \App\Models\Entity\Base\Entity
{
    
    public $session_id;
    
    public $session_user_id;
    
    public $session_key;
    
    public $session_from;
    
    public $session_last_activity;
    
    /**
     * 
     * @param int    $session_id
     * @param int    $session_user_id
     * @param string $session_key
     * @param int   $session_from
     * @param int   $session_last_activity
     */
    public function __construct(
        $session_id,
        $session_user_id,
        $session_key,
        $session_from,
        $session_last_activity
    ) {
        $this->session_id            = $session_id;
        $this->session_user_id       = $session_user_id;
        $this->session_key           = $session_key;
        $this->session_from          = $session_from;
        $this->session_last_activity = $session_last_activity;
    }
    
    public function get(\Dibi\Row $values)
    {
        return new Session(
            $values->session_id,
            $values->session_user_id,
            $values->session_key,
            $values->session_from,
            $values->session_last_activity
        );
    }
    
    /**
     * 
     * @return array
     */
    public function getArray()
    {
        $res = [];
        
        if (isset($this->session_id)) {
            $res['session_id'] = $this->session_id;
        }
        
        if (isset($this->session_user_id)) {
            $res['session_user_id'] = $this->session_user_id;
        }

        if (isset($this->session_key)) {
            $res['session_key'] = $this->session_key;
        }

        if (isset($this->session_from)) {
            $res['session_from'] = $this->session_from;
        }

        if (isset($this->session_last_activity)) {
            $res['session_last_activity'] = $this->session_last_activity;
        }        
        
        return $res;
    }
}
