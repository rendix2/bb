<?php

namespace App\Models\Entity\Base;

/**
 * Description of Entity
 *
 * @author rendi
 */
abstract class Entity
{
    public function __set($name, $value)
    {
        if (!property_exists($this, $name)) {
            throw new \Nette\MemberAccessException('Column {$name} does not exist.');
        }
    }
    
    public function __get($name)
    {
        if (!property_exists($this, $name)) {
            throw new \Nette\MemberAccessException('Column {$name} does not exist.');
        }
    }
    
    
    public function __isset($name)
    {
        if (!property_exists($this, $name)) {
            throw new \Nette\MemberAccessException('Column {$name} does not exist.');
        }
    }
    
    public function __unset($name)
    {
        if (!property_exists($this, $name)) {
            throw new \Nette\MemberAccessException('Column {$name} does not exist.');
        }
    }    

    /**
     * @return array
     */
    abstract function getArray();

    /**
     * 
     * @return \Nette\Utils\ArrayHas
     */
    public function getArrayHash()
    {
        return \Nette\Utils\ArrayHash::from($this->getArray());
    }    
    
}
