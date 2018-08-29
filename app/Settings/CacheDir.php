<?php

namespace App\Settings;

use Nette\Utils\Finder;
use SplFileInfo;

/**
 * Description of CacheDir
 *
 * @author rendix2
 */
class CacheDir
{
    /**
     * @var string $cacheDir
     */
    public $cacheDir;

    /**
     * CacheDir constructor.
     *
     * @param string $cacheDir
     */
    public function __construct($cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * @return int
     */
    public function getDirSize()
    {
        $size = 0;
        
        foreach (Finder::findFiles('*')->from($this->cacheDir) as $file) {
            /**
             * @var SplFileInfo $file
             */
            $size += $file->getSize();
        }
        
        return $size;
    }
}
