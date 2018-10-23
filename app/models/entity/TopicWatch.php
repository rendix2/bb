<?php

namespace App\Models\Entity;

/**
 * Description of TopicWatch
 *
 * @author rendix2
 */
class TopicWatch extends \App\Models\Entity\Base\Entity
{
    public $id;
    
    public $topic_id;
    
    public $user_id;
    
    /**
     * 
     * @param int $id
     * @param int $topic_id
     * @param int $user_id
     */
    public function __construct(
        $id,
        $topic_id,
        $user_id
    ) {
        $this->id       = $id === null ? null : (int)$id;
        $this->topic_id = (int)$topic_id;
        $this->user_id  = (int)$user_id;
    }
    
    /**
     * 
     * @param \Dibi\Row $values
     * 
     * @return \App\Models\Entity\TopicWatch
     */
    public static function get(\Dibi\Row $values)
    {
        return new TopicWatch(
            $values->id,
            $values->topic_id,
            $values->user_id            
        );
    } 
    
    /**
     * 
     * @return array
     */
    public function getArray()
    {
        $res = [];
        
        if (isset($this->id)) {
            $res['id'] = $this->id;
        }
        
        if (isset($this->topic_id)) {
            $res['topic_id'] = $this->topic_id;
        }

        if (isset($this->user_id)) {
            $res['user_id'] = $this->user_id;
        }
        
        return $res;
    }   
}
