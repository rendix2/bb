<?php

namespace App\Models\Entity;

/**
 * Description of Forum
 *
 * @author rendi
 */
class Forum 
{
    public $forum_id;
    
    public $forum_category_id;
    
    public $forum_name;
    
    public $forum_description;
    
    public $forum_active;
    
    public $forum_parent_id;     
    
    public $forum_order;
    
    public $forum_thank;
    
    public $forum_post_count;
    
    public $forum_topic_count;

    public $forum_post_add;
    
    public $forum_post_delete;
    
    public $forum_post_update;

    public $forum_topic_add;
       
    public $forum_topic_update;
    
    public $forum_topic_delete;
    
    public $forum_rules;

    public $forum_left;
    
    public $forum_right;

    /**
     * 
     * @param int    $forum_id
     * @param int    $forum_category_id
     * @param type   $forum_name
     * @param type   $forum_description
     * @param bool   $forum_active
     * @param int    $forum_parent_id
     * @param int    $forum_order
     * @param bool   $forum_thank
     * @param int    $forum_post_count
     * @param int    $forum_topic_count
     * @param bool   $forum_post_add
     * @param bool   $forum_post_delete
     * @param bool   $forum_post_update
     * @param bool   $forum_topic_add
     * @param bool   $forum_topic_update
     * @param bool   $forum_topic_delete
     * @param string $forum_rules
     * @param int    $forum_left
     * @param int    $forum_right
     */
    public function __construct(
        $forum_id,
        $forum_category_id,    
        $forum_name,
        $forum_description,
        $forum_active,
        $forum_parent_id,      
        $forum_order,
        $forum_thank,    
        $forum_post_count,    
        $forum_topic_count,
        $forum_post_add,
        $forum_post_delete,    
        $forum_post_update,
        $forum_topic_add,       
        $forum_topic_update,
        $forum_topic_delete,    
        $forum_rules,
        $forum_left,    
        $forum_right
    ) {
        $this->forum_id           = (int)$forum_id;
        $this->forum_category_id  = (int)$forum_category_id;
        $this->forum_name         = $forum_name;
        $this->forum_description  = (bool)$forum_description;
        $this->forum_active       = $forum_active;
        $this->forum_parent_id    = (int)$forum_parent_id;
        $this->forum_order        = (int)$forum_order;
        $this->forum_thank        = (int)$forum_thank;
        $this->forum_post_count   = (int)$forum_post_count;
        $this->forum_topic_count  = (int)$forum_topic_count;
        $this->forum_post_add     = (bool)$forum_post_add;
        $this->forum_post_delete  = (bool)$forum_post_delete;   
        $this->forum_post_update  = (bool)$forum_post_update;
        $this->forum_topic_add    = (bool)$forum_topic_add;   
        $this->forum_topic_update = (bool)$forum_topic_update;
        $this->forum_topic_delete = (bool)$forum_topic_delete;  
        $this->forum_rules        = $forum_rules;
        $this->forum_left         = (int)$forum_left;
        $this->forum_right        = (int)$forum_right;             
    }
    
    /**
     * 
     * @param \Dibi\Row $values
     * 
     * @return \App\Models\Entity\Forums
     */
    public static function get(\Dibi\Row $values)
    {
        return new Forum(
            $values->forum_id,
            $values->forum_category_id,
            $values->forum_name,
            $values->forum_description,
            $values->forum_active,
            $values->forum_parent_id, 
            $values->forum_order,
            $values->forum_thank,
            $values->forum_post_count,
            $values->forum_topic_count,
            $values->forum_post_add,
            $values->forum_post_delete,
            $values->forum_post_update,
            $values->forum_topic_add,
            $values->forum_topic_update,
            $values->forum_topic_delete,
            $values->forum_rules,
            $values->forum_left,
            $values->forum_right                                              
        );
    }     
    
    /**
     * 
     * @return array
     */
    public function getArray()
    {
        return [   
            'forum_id'           => $this->forum_id,
            'forum_category_id'  => $this->forum_category_id,
            'forum_name'         => $this->forum_name,            
            'forum_description'  => $this->forum_description,
            'forum_active'       => $this->forum_active,
            'forum_parent_id'    => $this->forum_parent_id,
            'forum_order'        => $this->forum_order, 
            'forum_thank'        => $this->forum_thank, 
            'forum_post_count'   => $this->forum_post_count, 
            'forum_topic_count'  => $this->forum_topic_count, 
            'forum_post_add'     => $this->forum_post_add, 
            'forum_post_delete'  => $this->forum_post_delete, 
            'forum_post_update'  => $this->forum_post_update, 
            'forum_topic_add'    => $this->forum_topic_add,             
            'forum_topic_update' => $this->forum_topic_update,
            'forum_topic_delete' => $this->forum_topic_delete,
            'forum_rules'        => $this->forum_rules,
            'forum_left'         => $this->forum_left,
            'forum_right'        => $this->forum_right,
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
