<?php

namespace App\Settings;

/**
 * Description of TempDir
 *
 * @author rendix2
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
