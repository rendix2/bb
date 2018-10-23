<?php

namespace App\Models\Entity;

/**
 * Description of Category
 *
 * @author rendix2
 */
class Category extends \App\Models\Entity\Base\Entity
{
    public $category_id;
    
    public $category_parent_id;
    
    public $category_name;
    
    public $category_active;

    public $category_left;
    
    public $category_right;

    public $category_order;
    
    /**
     * 
     * @param int    $category_id
     * @param int    $category_parent_id
     * @param string $category_name
     * @param bool   $category_active
     * @param int    $category_left
     * @param int    $category_right
     * @param int    $category_order
     */
    public function __construct(
        $category_id,
        $category_parent_id,    
        $category_name,
        $category_active,
        $category_left,
        $category_right,
        $category_order            
    ) {
        $this->category_id        = (int)$category_id;
        $this->category_parent_id = (int)$category_parent_id;
        $this->category_name      = $category_name;
        $this->category_active    = (bool)$category_active;
        $this->category_left      = $category_left === null ? null : (int)$category_left;
        $this->category_right     = $category_right === null ? null : (int)$category_right;
        $this->category_order     = (int)$category_order;
    }
    
    /**
     * 
     * @param \Dibi\Row $values
     * 
     * @return \App\Models\Entity\Category
     */
    public static function get(\Dibi\Row $values)
    {
        return new Category(
            $values->category_id,
            $values->category_parent_id,
            $values->category_name,
            $values->category_active,
            $values->category_left,
            $values->category_right, 
            $values->category_order
        );
    }    
    
    public function getArray()
    {        
        $res = [];
        
        if (isset($this->category_id)) {
            $res['category_id'] = $this->category_id;
        }
        
        if (isset($this->category_name)) {
            $res['category_name'] = $this->category_name;
        }

        if (isset($this->category_active)) {
            $res['category_active'] = $this->category_active;
        }

        if (isset($this->category_left)) {
            $res['category_left'] = $this->category_left;
        }

        if (isset($this->category_right)) {
            $res['category_right'] = $this->category_right;
        }

        if (isset($this->category_order)) {
            $res['category_order'] = $this->category_order;
        }
        
        return $res;
    }    
}
