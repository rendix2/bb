<?php

namespace App\Controls;

/**
 * Description of TempDir
 *
 * @author rendi
 */
class TempDir
{
    public $tempDir;

    /**
     * TempDir constructor.
     *
     * @param $tempDir
     */
    public function __construct($tempDir)
    {
        $this->tempDir = $tempDir;
    }
}
