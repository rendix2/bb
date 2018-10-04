<?php

namespace App\Models\Entity;

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
        return [
            'topic_id'            => $this->topic_id, 
            'topic_category_id'   => $this->topic_category_id,
            'topic_forum_id'      => $this->topic_forum_id, 
            'topic_user_id'       => $this->topic_user_id, 
            'topic_name'          => $this->topic_name, 
            'topic_post_count'    => $this->topic_post_count, 
            'topic_add_time'      => $this->topic_add_time, 
            'topic_locked'        => $this->topic_locked, 
            'topic_view_count'    => $this->topic_view_count, 
            'topic_first_post_id' => $this->topic_first_post_id, 
            'topic_first_user_id' => $this->topic_first_user_id, 
            'topic_last_post_id'  => $this->topic_last_post_id, 
            'topic_last_user_id'  => $this->topic_last_user_id, 
            'topic_order'         => $this->topic_order 
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
