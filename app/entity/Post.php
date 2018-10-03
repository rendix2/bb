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
    
    /**
     * 
     * @param int    $post_id
     * @param int    $post_user_id
     * @param int    $post_category_id
     * @param int    $post_forum_id
     * @param int    $post_topic_id
     * @param string $post_title
     * @param string $post_text
     * @param int    $post_add_time
     * @param string $post_add_user_ip
     * @param strin  $post_edit_user_ip
     * @param int    $post_edit_count
     * @param int    $post_last_edit_time
     * @param bool   $post_locked
     * @param int    $post_order
     */
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
    
    /**
     * 
     * @param \Dibi\Row $values
     * 
     * @return \App\Entity\Post
     */
    public static function get(\Dibi\Row $values)
    {
        return new Post(
            $values->post_id,
            $values->post_user_id,
            $values->post_category_id,
            $values->post_forum_id,
            $values->post_topic_id,
            $values->post_title, 
            $values->post_text,
            $values->post_add_time,
            $values->post_add_user_ip,
            $values->post_edit_user_ip,
            $values->post_edit_count, 
            $values->post_last_edit_time,
            $values->post_locked,
            $values->post_order
        );
    }
    
}