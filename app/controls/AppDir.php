<?php

namespace App\Controls;

/**
 * Description of AppDir
 *
 * @author rendi
 */
/**
 * Class AppDir
 *
 * @package App\Controls
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
     * @param $appDir
     */
    public function __construct($appDir)
    {
        $this->appDir = $appDir;
    }
}
