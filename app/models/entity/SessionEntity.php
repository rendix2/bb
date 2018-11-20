<?php

namespace App\Models\Entity;

use App\Models\Entity\Base\Entity;
use Dibi\Row;
use Nette\Utils\ArrayHash;

/**
 * Description of SessionEntity
 *
 * @author rendix2
 * @package App\Models\Entity
 */
class SessionEntity extends Entity
{
    /**
     *
     * @var int $session_id
     */
    private $session_id;

    /**
     *
     * @var int $session_user_id
     */
    private $session_user_id;
    
    /**
     *
     * @var string $session_key
     */    
    private $session_key;
   
    /**
     *
     * @var int $session_from
     */    
    private $session_from;
    
    /**
     *
     * @var int $session_last_activity
     */    
    private $session_last_activity;
    
    public function getSession_id()
    {
        return $this->session_id;
    }

    public function getSession_user_id()
    {
        return $this->session_user_id;
    }

    public function getSession_key()
    {
        return $this->session_key;
    }

    public function getSession_from()
    {
        return $this->session_from;
    }

    public function getSession_last_activity()
    {
        return $this->session_last_activity;
    }

    public function setSession_id($session_id)
    {
        $this->session_id = self::makeInt($session_id);
        return $this;
    }

    public function setSession_user_id($session_user_id)
    {
        $this->session_user_id = self::makeInt($session_user_id);
        return $this;
    }

    public function setSession_key($session_key)
    {
        $this->session_key = $session_key;
        return $this;
    }

    public function setSession_from($session_from)
    {
        $this->session_from = self::makeInt($session_from);
        return $this;
    }

    public function setSession_last_activity($session_last_activity)
    {
        $this->session_last_activity = self::makeInt($session_last_activity);
        return $this;
    }

    /**
     * 
     * @param Row $values
     * 
     * @return SessionEntity
     */
    public static function setFromRow(Row $values)
    {
        $session = new SessionEntity();
      
        if (isset($values->session_id)) {
            $session->setSession_id($values->session_id);
        }
        
        if (isset($values->session_user_id)) {
            $session->setSession_user_id($values->session_user_id);
        }
        
        if (isset($values->session_key)) {
            $session->setSession_key($values->session_key);
        }
        
        if (isset($values->session_from)) {
            $session->setSession_from($values->session_from);
        }
        
        if (isset($values->session_last_activity)) {
            $session->setSession_last_activity($values->session_last_activity);
        }
        
        return $session;
    }
    
    /**
     * 
     * @param ArrayHash $values
     * 
     * @return SessionEntity
     */
    public static function setFromArrayHash(ArrayHash $values)
    {
        $session = new SessionEntity();
      
        if (isset($values->session_id)) {
            $session->setSession_id($values->session_id);
        }
        
        if (isset($values->session_user_id)) {
            $session->setSession_user_id($values->session_user_id);
        }
        
        if (isset($values->session_key)) {
            $session->setSession_key($values->session_key);
        }
        
        if (isset($values->session_from)) {
            $session->setSession_from($values->session_from);
        }
        
        if (isset($values->session_last_activity)) {
            $session->setSession_last_activity($values->session_last_activity);
        }
        
        return $session;
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
