<?php

namespace App\Settings;

/**
 * Class WwwDir
 *
 * @package App\Controls
 * @author rendix2
 */
class WwwDir
{
    /**
     * @var string $wwwDir
     */
    public $wwwDir;

    /**
     * WwwDir constructor.
     *
     * @param string $wwwDir
     */
    public function __construct($wwwDir)
    {
        $this->wwwDir = $wwwDir;
    }
}
