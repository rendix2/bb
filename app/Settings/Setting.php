<?php

namespace App\Settings;

/**
 * Description of Setting
 *
 * @author rendix2
 * @package App\Settings
 */
abstract class Setting
{
    /**
     *
     * @var string|array $setting
     */
    private $setting;
    
    /**
     * Setting constructor.
     *
     * @param string|array $setting
     */
    public function __construct($setting)
    {
        $this->setting = $setting;
    }
    
    /**
     * Setting destructor.
     */
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
