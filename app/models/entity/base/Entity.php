<?php

namespace App\Models\Entity\Base;

use Nette\MemberAccessException;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;

/**
 * Description of Entity
 *
 * @author rendi
 */
abstract class Entity
{
    use \Nette\SmartObject;
    
    /**
     *
     */
    public function __construct()
    {
        foreach ($this as $key => $value) {
            $this->{$key} = null;
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        foreach ($this as $key => $value) {
            $this->{$key} = null;
        }
    }

    /**
     *
     * @param string $name
     * @param mixed  $value
     *
     * @throws MemberAccessException
     */
    /*
    public function __set($name, $value)
    {
        if (!property_exists($this, $name)) {
            throw new MemberAccessException("Column '{$name}' of '".get_class($this)."' does not exist.");
        }
    }
     *
     */

    /**
     *
     * @param string $name
     *
     * @throws MemberAccessException
     */
    /*
    public function __get($name)
    {
        if (!property_exists($this, $name)) {
            throw new MemberAccessException("Column {$name} of '".get_class($this)."' does not exist.");
        }
    }
     *
     */
    
    /**
     *
     * @param string $name
     *
     * @throws MemberAccessException
     */
    /*
    public function __isset($name)
    {
        if (!property_exists($this, $name)) {
            throw new MemberAccessException("Column '{$name}' of '".get_class($this)."' does not exist.");
        }
    }
     *
     */
    
    /**
     *
     * @param string $name
     *
     * @throws MemberAccessException
     */
    /*
    public function __unset($name)
    {
        if (!property_exists($this, $name)) {
            throw new MemberAccessException("Column '{$name}' of '".get_class($this)."' does not exist.");
        }
    }
     *
     */

    /**
     * @return array
     */
    abstract public function getArray();

    /**
     *
     * @return ArrayHash
     */
    public function getArrayHash()
    {
        return ArrayHash::from($this->getArray());
    }
    
    /**
     *
     * @param mixed $var
     *
     * @return bool|null
     */
    public static function makeBool($var)
    {
        return $var === null ? null : (bool) $var;
    }
    
    /**
     *
     * @param mixed $var
     *
     * @return int|null
     */
    public static function makeInt($var)
    {
        return $var === null ? null : (int) $var;
    }
   
    /**
     *
     * @param DateTime $var
     *
     * @return int|null
     */
    public static function makeTimestamp(DateTime $var = null)
    {
        return $var === null ? null : $var->getTimestamp();
    }
}
