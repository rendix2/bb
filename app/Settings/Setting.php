<?php

namespace App\Settings;

/**
 * Description of Setting
 *
 * @author rendi
 */
abstract class Setting 
{
    private $setting;
    
    public function __construct($setting)
    {
        $this->setting = $setting;
    }
    
    public function get()
    {
        return $this->setting;
    }
}
