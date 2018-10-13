<?php

namespace App\Models\Entity;

/**
 * Description of Thank
 *
 * @author rendi
 */
class Thank extends \App\Models\Entity\Base\Entity
{
    public $thank_id;
    
    public $thank_forum_id;
    
    public $thank_topic_id;
    
    public $thank_user_id;
    
    public $thank_time;
    
    public $thank_user_ip;
    
    /**
     * 
     * @param int    $thank_id
     * @param int    $thank_forum_id
     * @param int    $thank_topic_id
     * @param int    $thank_user_id
     * @param int    $thank_time
     * @param string $thank_user_ip
     */
    public function __construct(
            $thank_id,
            $thank_forum_id,
            $thank_topic_id,
            $thank_user_id,
            $thank_time,
            $thank_user_ip
    ) {
        $this->thank_id       = $thank_id;    
        $this->thank_forum_id = $thank_forum_id;    
        $this->thank_topic_id = $thank_topic_id;    
        $this->thank_user_id = $thank_user_id;    
        $this->thank_time    = $thank_time;    
        $this->thank_user_ip = $thank_user_ip;
    }
    
    /**
     * 
     * @param \Dibi\Row $values
     * 
     * @return \App\Models\Entity\Thank
     */
    public static function get(\Dibi\Row $values)
    {
        return new Thank(
            $values->thank_id,
            $values->thank_forum_id,
            $values->thank_topic_id,
            $values->thank_user_id,
            $values->thank_time,
            $values->thank_user_ip
        );
    }    
    
    /**
     * 
     * @return array
     */
    public function getArray()
    {
        $res = [];
        
        if (isset($this->thank_id)) {
            $res['thank_id'] = $this->thank_id;
        }
        
        if (isset($this->thank_forum_id)) {
            $res['thank_forum_id'] = $this->thank_forum_id;
        }

        if (isset($this->thank_topic_id)) {
            $res['thank_topic_id'] = $this->thank_topic_id;
        }

        if (isset($this->thank_user_id)) {
            $res['thank_user_id'] = $this->thank_user_id;
        }

        if (isset($this->thank_time)) {
            $res['thank_time'] = $this->thank_time;
        }

        if (isset($this->thank_user_ip)) {
            $res['thank_user_ip'] = $this->thank_user_ip;
        }        
        
        return $res;
    }
}
