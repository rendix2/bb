<?php

namespace App\Controls;

/**
 * Description of WwwDir
 *
 * @author rendi
 */
/**
 * Class WwwDir
 *
 * @package App\Controls
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
