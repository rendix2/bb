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
    
}
