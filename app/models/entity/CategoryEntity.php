<?php

namespace App\Models\Entity;

use App\Models\Entity\Base\Entity;
use Dibi\Row;
use Nette\Utils\ArrayHash;

/**
 * Description of CategoryEntity
 *
 * @author rendix2
 * @package App\Models\Entity
 */
class CategoryEntity extends Entity
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
     * @param $category_id
     *
     * @return CategoryEntity
     */
    public function setCategory_id($category_id)
    {
        $this->category_id = self::makeInt($category_id);
        return $this;
    }

    /**
     * @param $category_parent_id
     *
     * @return CategoryEntity
     */
    public function setCategory_parent_id($category_parent_id)
    {
        $this->category_parent_id = self::makeInt($category_parent_id);
        return $this;
    }

    /**
     * @param $category_name
     *
     * @return CategoryEntity
     */
    public function setCategory_name($category_name)
    {
        $this->category_name = $category_name;
        return $this;
    }

    /**
     * @param $category_active
     *
     * @return CategoryEntity
     */
    public function setCategory_active($category_active)
    {
        $this->category_active = self::makeBool($category_active);
        return $this;
    }

    /**
     * @param $category_left
     *
     * @return CategoryEntity
     */
    public function setCategory_left($category_left)
    {
        $this->category_left = self::makeInt($category_left);
        return $this;
    }

    /**
     * @param $category_right
     *
     * @return CategoryEntity
     */
    public function setCategory_right($category_right)
    {
        $this->category_right = self::makeInt($category_right);
        return $this;
    }

    /**
     * @param $category_order
     *
     * @return CategoryEntity
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
     * @return CategoryEntity
     */
    public static function setFromRow(Row $values)
    {
        $categoryEntity = new CategoryEntity();
        
        if (isset($values->category_id)) {
            $categoryEntity->setCategory_id($values->category_id);
        }
        
        if (isset($values->category_parent_id)) {
            $categoryEntity->setCategory_parent_id($values->category_parent_id);
        }
        
        if (isset($values->category_name)) {
            $categoryEntity->setCategory_name($values->category_name);
        }
         
        if (isset($values->category_active)) {
            $categoryEntity->setCategory_active($values->category_active);
        }
        
        if (isset($values->category_left)) {
            $categoryEntity->setCategory_left($values->category_left);
        }
        
        if (isset($values->category_right)) {
            $categoryEntity->setCategory_right($values->category_right);
        }
        
        if (isset($values->category_order)) {
            $categoryEntity->setCategory_order($values->category_order);
        }

        return $categoryEntity;
    }
    
    /**
     *
     * @param ArrayHash $values
     *
     * @return CategoryEntity
     */
    public static function setFromArrayHash(ArrayHash $values)
    {
        $categoryEntity = new CategoryEntity();
        
        if (isset($values->category_id)) {
            $categoryEntity->setCategory_id($values->category_id);
        }
        
        if (isset($values->category_parent_id)) {
            $categoryEntity->setCategory_parent_id($values->category_parent_id);
        }
        
        if (isset($values->category_name)) {
            $categoryEntity->setCategory_name($values->category_name);
        }
         
        if (isset($values->category_active)) {
            $categoryEntity->setCategory_active($values->category_active);
        }
        
        if (isset($values->category_left)) {
            $categoryEntity->setCategory_left($values->category_left);
        }
        
        if (isset($values->category_right)) {
            $categoryEntity->setCategory_right($values->category_right);
        }
        
        if (isset($values->category_order)) {
            $categoryEntity->setCategory_order($values->category_order);
        }

        return $categoryEntity;
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
