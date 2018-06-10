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

    public $cacheDir;
    
    public function __construct($cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }
    
    public function getDirSize()
    {
        $size = 0;
        
        foreach (Finder::findFiles('*')->from($this->cacheDir) as $file) {
            $size += $file->getSize();
        }
        
        return $size;
    }
    
}
