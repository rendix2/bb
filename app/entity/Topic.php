<?php

namespace App\Entity;

/**
 * Description of Topic
 *
 * @author rendi
 */
class Topic
{
        public $topic_id;
        public $topic_category_id;
        public $topic_forum_id;
        public $topic_user_id;
        public $topic_name;
        public $topic_post_count;
        public $topic_add_time;
        public $toopic_locked;
        public $topic_view_count;
        public $topic_first_post_id;
        public $topic_first_user_id;
        public $topic_last_post_id;
        public $topic_last_user_id;
        public $topic_order;
        
    /**
     * 
     * @param int    $topic_id
     * @param int    $topic_category_id
     * @param int    $topic_forum_id
     * @param int    $topic_user_id
     * @param string $topic_name
     * @param int    $topic_post_count
     * @param int    $topic_add_time
     * @param bool   $toopic_locked
     * @param int    $topic_view_count
     * @param int    $topic_first_post_id
     * @param int    $topic_first_user_id
     * @param int    $topic_last_post_id
     * @param int    $topic_last_user_id
     * @param int    $topic_order
     */    
    public function __construct(
        $topic_id,
        $topic_category_id,
        $topic_forum_id,
        $topic_user_id,
        $topic_name,
        $topic_post_count,
        $topic_add_time,
        $toopic_locked,
        $topic_view_count,
        $topic_first_post_id,
        $topic_first_user_id,
        $topic_last_post_id,
        $topic_last_user_id,
        $topic_order           
    ) {
        $this->topic_id            = $topic_id;
        $this->topic_category_id   = $topic_category_id;
        $this->topic_forum_id      = $topic_forum_id;
        $this->topic_user_id       = $topic_user_id;
        $this->topic_name          = $topic_name;
        $this->topic_post_count    = $topic_post_count;
        $this->topic_add_time      = $topic_add_time;
        $this->toopic_locked       = $toopic_locked;
        $this->topic_view_count    = $topic_view_count;
        $this->topic_first_post_id = $topic_first_post_id;
        $this->topic_first_user_id = $topic_first_user_id;
        $this->topic_last_post_id  = $topic_last_post_id;
        $this->otpic_last_user_id  = $topic_last_user_id;
        $this->topic_order         = $topic_order;
    }
    
    /**
     * 
     * @param \Dibi\Row $values
     * 
     * @return \App\Entity\Topic
     */
    public function get(\Dibi\Row $values)
    {
        return new Topic(
            $values->topic_id, 
            $values->topic_category_id,
            $values->topic_forum_id, 
            $values->topic_user_id, 
            $values->topic_name, 
            $values->topic_post_count, 
            $values->topic_add_time, 
            $values->toopic_locked, 
            $values->topic_view_count, 
            $values->topic_first_post_id, 
            $values->topic_first_user_id, 
            $values->topic_last_post_id, 
            $values->topic_last_user_id, 
            $values->topic_order
        );
    }
    
}
