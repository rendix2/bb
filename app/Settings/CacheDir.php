<?php

namespace App\Settings;

use Nette\Utils\Finder;
use SplFileInfo;

/**
 * Description of CacheDir
 *
 * @author rendix2
 * @package App\Settings
 */
class CacheDir extends Setting
{
    /**
     * @return int
     */
    public function getDirSize()
    {
        $size = 0;
        
        foreach (Finder::findFiles('*')->from($this->get()) as $file) {
            /**
             * @var SplFileInfo $file
             */
            $size += $file->getSize();
        }
        
        return $size;
    }
}
