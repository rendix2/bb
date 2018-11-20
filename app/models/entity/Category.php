<?php

namespace App\Models\Entity;

use App\Models\Entity\Base\Entity;
use Dibi\Row;
use Nette\Utils\ArrayHash;

/**
 * Description of Category
 *
 * @author rendix2
 */
class Category extends Entity
{
    /**
     *
     * @var int $category_id
     */
    private $category_id;
    
    /**
     *
     * @var int $category_parent_id
     */
    private $category_parent_id;
    
    /**
     *
     * @var string $category_name
     */
    private $category_name;
    
    /**
     *
     * @var bool $category_active
     */
    private $category_active;

    /**
     *
     * @var int $category_left
     */
    private $category_left;
    
    /**
     *
     * @var int $category_right
     */
    private $category_right;

    /**
     *
     * @var int $category_order
     */
    private $category_order;

    /**
     * @return int
     */
    public function getCategory_id()
    {
        return $this->category_id;
    }

    /**
     * @return int
     */
    public function getCategory_parent_id()
    {
        return $this->category_parent_id;
    }

    /**
     * @return string
     */
    public function getCategory_name()
    {
        return $this->category_name;
    }

    /**
     * @return bool
     */
    public function getCategory_active()
    {
        return $this->category_active;
    }

    /**
     * @return int
     */
    public function getCategory_left()
    {
        return $this->category_left;
    }

    /**
     * @return int
     */
    public function getCategory_right()
    {
        return $this->category_right;
    }

    /**
     * @return int
     */
    public function getCategory_order()
    {
        return $this->category_order;
    }

    /**
     * @param int $category_id
     *
     * @return Category
     */
    public function setCategory_id($category_id)
    {
        $this->category_id = self::makeInt($category_id);
        return $this;
    }

    /**
     * @param int $category_parent_id
     *
     * @return Category
     */
    public function setCategory_parent_id($category_parent_id)
    {
        $this->category_parent_id = self::makeInt($category_parent_id);
        return $this;
    }

    /**
     * @param string $category_name
     *
     * @return Category
     */
    public function setCategory_name($category_name)
    {
        $this->category_name = $category_name;
        return $this;
    }

    /**
     * @param $category_active
     *
     * @return \App\Authorization\Scopes\Category
     */
    public function setCategory_active($category_active)
    {
        $this->category_active = self::makeBool($category_active);
        return $this;
    }

    /**
     * @param $category_left
     * @return $this
     */
    public function setCategory_left($category_left)
    {
        $this->category_left = self::makeInt($category_left);
        return $this;
    }

    /**
     * @param $category_right
     * @return $this
     */
    public function setCategory_right($category_right)
    {
        $this->category_right = self::makeInt($category_right);
        return $this;
    }

    /**
     * @param $category_order
     * @return $this
     */
    public function setCategory_order($category_order)
    {
        $this->category_order = self::makeInt($category_order);
        return $this;
    }

    /**
     *
     * @param Row $values
     *
     * @return Category
     */
    public static function setFromRow(Row $values)
    {
        $category = new Category();
        
        if (isset($values->category_id)) {
            $category->setCategory_id($values->category_id);
        }
        
        if (isset($values->category_parent_id)) {
            $category->setCategory_parent_id($values->category_parent_id);
        }
        
        if (isset($values->category_name)) {
            $category->setCategory_name($values->category_name);
        }
         
        if (isset($values->category_active)) {
            $category->setCategory_active($values->category_active);
        }
        
        if (isset($values->category_left)) {
            $category->setCategory_left($values->category_left);
        }
        
        if (isset($values->category_right)) {
            $category->setCategory_right($values->category_right);
        }
        
        if (isset($values->category_order)) {
            $category->setCategory_order($values->category_order);
        }

        return $category;
    }
    
    /**
     * 
     * @param ArrayHash $values
     * 
     * @return Category
     */
    public static function setFromArrayHash(ArrayHash $values)
    {
        $category = new Category();
        
        if (isset($values->category_id)) {
            $category->setCategory_id($values->category_id);
        }
        
        if (isset($values->category_parent_id)) {
            $category->setCategory_parent_id($values->category_parent_id);
        }
        
        if (isset($values->category_name)) {
            $category->setCategory_name($values->category_name);
        }
         
        if (isset($values->category_active)) {
            $category->setCategory_active($values->category_active);
        }
        
        if (isset($values->category_left)) {
            $category->setCategory_left($values->category_left);
        }
        
        if (isset($values->category_right)) {
            $category->setCategory_right($values->category_right);
        }
        
        if (isset($values->category_order)) {
            $category->setCategory_order($values->category_order);
        }

        return $category;
    }

    /**
     * @return array
     */
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
