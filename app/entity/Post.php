<?php

namespace App\Entity;

/**
 * Description of Post
 *
 * @author rendi
 */
class Post
{
    public $post_id;
    
    public $post_user_id;

    public $post_category_id;
    
    public $post_forum_id;

    public $post_topic_id;
    
    public $post_title;
    
    public $post_text;
    
    public $post_add_time;
    
    public $post_add_user_ip;
    
    public $post_edit_user_ip;
    
    public $post_edit_count;
    
    public $post_last_edit_time;
    
    public $post_locked;
    
    public $post_order;
    
    public function __construct(
        $post_id,
        $post_user_id,
        $post_category_id,
        $post_forum_id,
        $post_topic_id,
        $post_title,
        $post_text,
        $post_add_time,
        $post_add_user_ip,
        $post_edit_user_ip,
        $post_edit_count,
        $post_last_edit_time,
        $post_locked,
        $post_order
    ) {
        $this->post_id             = $post_id;
        $this->post_user_id        = $post_user_id;
        $this->post_category_id    = $post_category_id;
        $this->post_forum_id       = $post_forum_id;
        $this->post_topic_id       = $post_topic_id;
        $this->post_title          = $post_title;
        $this->post_text           = $post_text;
        $this->post_add_time       = $post_add_time;
        $this->post_add_user_ip    = $post_add_user_ip;
        $this->post_edit_user_ip   = $post_edit_user_ip;
        $this->post_edit_count     = $post_edit_count;
        $this->post_last_edit_time = $post_last_edit_time;
        $this->post_locked         = $post_locked;
        $this->post_order          = $post_order ;
    }
    
}