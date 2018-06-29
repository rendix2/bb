<?php

namespace App\Controls;

use Nette\Utils\Finder;

/**
 * Description of CacheDir
 *
 * @author rendi
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
