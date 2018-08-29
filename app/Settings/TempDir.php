<?php

namespace App\Settings;

/**
 * Description of TempDir
 *
 * @author rendix2
 */
class TempDir
{
    /**
     * @var string $tempDir
     */
    public $tempDir;

    /**
     * TempDir constructor.
     *
     * @param string $tempDir
     */
    public function __construct($tempDir)
    {
        $this->tempDir = $tempDir;
    }
}
