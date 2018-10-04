<?php

namespace App\Models\Entity;

/**
 * Description of TopicWatch
 *
 * @author rendi
 */
class TopicWatch
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
        $this->id       = (int)$id;
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
        return [
            'id'       => $this->id, 
            'topic_id' => $this->topic_id,
            'user_id'  => $this->user_id,  
        ];
    }
    
    /**
     * 
     * @return \Nette\Utils\ArrayHas
     */
    public function getArrayHash()
    {
        return \Nette\Utils\ArrayHash::from($this->getArray());
    }
}
