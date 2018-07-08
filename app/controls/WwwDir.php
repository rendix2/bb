<?php

namespace App\Controls;

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
     * @param $wwwDir
     */
    public function __construct($wwwDir)
    {
        $this->wwwDir = $wwwDir;
    }
}
