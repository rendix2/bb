<?php

namespace App\Models\Entity;

/**
 * Description of Topic
 *
 * @author rendi
 */
class Topic extends \App\Models\Entity\Base\Entity
{
    public $topic_id;
    public $topic_category_id;
    public $topic_forum_id;
    public $topic_user_id;
    public $topic_name;
    public $topic_post_count;
    public $topic_add_time;
    public $topic_locked;
    public $topic_view_count;
    public $topic_first_post_id;
    public $topic_first_user_id;
    public $topic_last_post_id;
    public $topic_last_user_id;
    public $topic_order;
        
    /**
     *
     * @var \App\Models\Entity\Post $post
     */
    public $post;

    /**
     * 
     * @param int    $topic_id
     * @param int    $topic_category_id
     * @param int    $topic_forum_id
     * @param int    $topic_user_id
     * @param string $topic_name
     * @param int    $topic_post_count
     * @param int    $topic_add_time
     * @param bool   $topic_locked
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
        $topic_locked,
        $topic_view_count,
        $topic_first_post_id,
        $topic_first_user_id,
        $topic_last_post_id,
        $topic_last_user_id,
        $topic_order,
        \App\Models\Entity\Post $post = null
    ) {
        $this->topic_id            = (int)$topic_id;
        $this->topic_category_id   = (int)$topic_category_id;
        $this->topic_forum_id      = (int)$topic_forum_id;
        $this->topic_user_id       = (int)$topic_user_id;
        $this->topic_name          = $topic_name;
        $this->topic_post_count    = (int)$topic_post_count;
        $this->topic_add_time      = (int)$topic_add_time;
        $this->topic_locked        = (bool)$topic_locked;
        $this->topic_view_count    = (int)$topic_view_count;
        $this->topic_first_post_id = (int)$topic_first_post_id;
        $this->topic_first_user_id = (int)$topic_first_user_id;
        $this->topic_last_post_id  = (int)$topic_last_post_id;
        $this->topic_last_user_id  = (int)$topic_last_user_id;
        $this->topic_order         = (int)$topic_order;
        $this->post                = $post;
    }
    
    /**
     * 
     * @param \Dibi\Row $values
     * 
     * @return \App\Models\Entity\Topic
     */
    public static function get(\Dibi\Row $values)
    {
        return new Topic(
            $values->topic_id, 
            $values->topic_category_id,
            $values->topic_forum_id, 
            $values->topic_user_id, 
            $values->topic_name, 
            $values->topic_post_count, 
            $values->topic_add_time, 
            $values->topic_locked, 
            $values->topic_view_count, 
            $values->topic_first_post_id, 
            $values->topic_first_user_id, 
            $values->topic_last_post_id, 
            $values->topic_last_user_id, 
            $values->topic_order
        );
    }
    
    /**
     * 
     * @return array
     */
    public function getArray()
    {
        $res = [];
        
        if (isset($this->topic_id)) {
            $res['topic_id'] = $this->topic_id;
        }
        
        if (isset($this->topic_category_id)) {
            $res['topic_category_id'] = $this->topic_category_id;
        }

        if (isset($this->topic_forum_id)) {
            $res['topic_forum_id'] = $this->topic_forum_id;
        }

        if (isset($this->topic_user_id)) {
            $res['topic_user_id'] = $this->topic_user_id;
        }

        if (isset($this->topic_name)) {
            $res['topic_name'] = $this->topic_name;
        }    
        
        if (isset($this->topic_post_count)) {
            $res['topic_post_count'] = $this->topic_post_count;
        } 

        if (isset($this->topic_add_time)) {
            $res['topic_add_time'] = $this->topic_add_time;
        } 

        if (isset($this->topic_locked)) {
            $res['topic_locked'] = $this->topic_locked;
        } 

        if (isset($this->topic_view_count)) {
            $res['topic_view_count'] = $this->topic_view_count;
        }    
        
        if (isset($this->topic_first_post_id)) {
            $res['topic_first_post_id'] = $this->topic_first_post_id;
        }  

        if (isset($this->topic_first_user_id)) {
            $res['topic_first_user_id'] = $this->topic_first_user_id;
        }  

        if (isset($this->topic_last_post_id)) {
            $res['topic_last_post_id'] = $this->topic_last_post_id;
        }          
        
        if (isset($this->topic_last_user_id)) {
            $res['topic_last_user_id'] = $this->topic_last_user_id;
        }    

        if (isset($this->topic_order)) {
            $res['topic_order'] = $this->topic_order;
        }            
        
        return $res;
    }
}
