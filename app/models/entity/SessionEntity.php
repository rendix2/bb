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

    /**
     * @return int
     */
    public function getSession_id()
    {
        return $this->session_id;
    }

    /**
     * @return int
     */
    public function getSession_user_id()
    {
        return $this->session_user_id;
    }

    /**
     * @return string
     */
    public function getSession_key()
    {
        return $this->session_key;
    }

    /**
     * @return int
     */
    public function getSession_from()
    {
        return $this->session_from;
    }

    /**
     * @return int
     */
    public function getSession_last_activity()
    {
        return $this->session_last_activity;
    }

    /**
     * @param $session_id
     *
     * @return SessionEntity
     */
    public function setSession_id($session_id)
    {
        $this->session_id = self::makeInt($session_id);
        return $this;
    }

    /**
     * @param $session_user_id
     *
     * @return SessionEntity
     */
    public function setSession_user_id($session_user_id)
    {
        $this->session_user_id = self::makeInt($session_user_id);
        return $this;
    }

    /**
     * @param $session_key
     *
     * @return SessionEntity
     */
    public function setSession_key($session_key)
    {
        $this->session_key = $session_key;
        return $this;
    }

    /**
     * @param $session_from
     *
     * @return SessionEntity
     */
    public function setSession_from($session_from)
    {
        $this->session_from = self::makeInt($session_from);
        return $this;
    }

    /**
     * @param $session_last_activity
     *
     * @return SessionEntity
     */
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
        $sessionEntity = new SessionEntity();
      
        if (isset($values->session_id)) {
            $sessionEntity->setSession_id($values->session_id);
        }
        
        if (isset($values->session_user_id)) {
            $sessionEntity->setSession_user_id($values->session_user_id);
        }
        
        if (isset($values->session_key)) {
            $sessionEntity->setSession_key($values->session_key);
        }
        
        if (isset($values->session_from)) {
            $sessionEntity->setSession_from($values->session_from);
        }
        
        if (isset($values->session_last_activity)) {
            $sessionEntity->setSession_last_activity($values->session_last_activity);
        }
        
        return $sessionEntity;
    }
    
    /**
     *
     * @param ArrayHash $values
     *
     * @return SessionEntity
     */
    public static function setFromArrayHash(ArrayHash $values)
    {
        $sessionEntity = new SessionEntity();
      
        if (isset($values->session_id)) {
            $sessionEntity->setSession_id($values->session_id);
        }
        
        if (isset($values->session_user_id)) {
            $sessionEntity->setSession_user_id($values->session_user_id);
        }
        
        if (isset($values->session_key)) {
            $sessionEntity->setSession_key($values->session_key);
        }
        
        if (isset($values->session_from)) {
            $sessionEntity->setSession_from($values->session_from);
        }
        
        if (isset($values->session_last_activity)) {
            $sessionEntity->setSession_last_activity($values->session_last_activity);
        }
        
        return $sessionEntity;
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
