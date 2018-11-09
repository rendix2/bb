<?php

namespace App\Settings;

/**
 * Description of Setting
 *
 * @author rendi
 */
abstract class Setting 
{
    /**
     *
     * @var string|array $setting
     */
    private $setting;
    
    /**
     * 
     * @param string|array $setting
     */
    public function __construct($setting)
    {
        $this->setting = $setting;
    }
    
    public function __destruct()
    {
        $this->setting = null;
    }

    /**
     * 
     * @return string|array
     */
    public function get()
    {
        return $this->setting;
    }
}
