<?php

namespace App\Models\Entity;

/**
 * Description of Thank
 *
 * @author rendi
 */
class Thank 
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
        return [
            'thank_id'       => $this->thank_id, 
            'thank_forum_id' => $this->thank_forum_id,
            'topic_forum_id' => $this->topic_forum_id, 
            'thank_topic_id' => $this->thank_topic_id, 
            'thank_user_id'  => $this->thank_user_id, 
            'thank_time'     => $this->thank_time, 
            'thank_user_ip'  => $this->thank_user_ip, 
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
