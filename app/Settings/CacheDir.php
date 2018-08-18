<?php

namespace App\Settings;

use Nette\Utils\Finder;

/**
 * Description of CacheDir
 *
 * @author rendix2
 */
class CacheDir
{
    /**
     * @var CacheDir $cacheDir
     */
    public $cacheDir;

    /**
     * CacheDir constructor.
     *
     * @param $cacheDir
     */
    public function __construct($cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * @return CacheDir
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
             * @var \SplFileInfo $file
             */
            $size += $file->getSize();
        }
        
        return $size;
    }
}
