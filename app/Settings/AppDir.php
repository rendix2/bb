<?php

namespace App\Settings;

/**
 * Class AppDir
 *
 * @package App\Controls
 * @author rendix2
 */
class AppDir
{
    /**
     * @var string $appDir
     */
    public $appDir;

    /**
     * AppDir constructor.
     *
     * @param string $appDir
     */
    public function __construct($appDir)
    {
        $this->appDir = $appDir;
    }

    /**
     * @return string
     */
    public function getAppDir()
    {
        return $this->appDir;
    }
}
