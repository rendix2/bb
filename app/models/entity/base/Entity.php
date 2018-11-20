<?php

namespace App\Models\Entity\Base;

use Nette\SmartObject;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;

/**
 * Description of Entity
 *
 * @author rendix2
 * @package App\Models\Entity\Base
 */
abstract class Entity
{
    use SmartObject;
    
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
