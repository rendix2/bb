<?php

namespace App\Controls;

use Nette\Utils\Finder;
use SplFileInfo;

/**
 * Description of Avatars
 *
 * @author rendi
 */
class Avatars
{
    /**
     * @var string
     */
    const DIR = 'dir';
    
    /**
     * @var string
     */
    const MAX_HEIGHT = 'maxHeight';

    /**
     * @var string
     */
    const MAX_WIDTH = 'maxWidth';
    
    /**
     * @var string
     */
    const MAX_FILE_SIZE = 'maxFileSize';
    
    /**
     * @var string
     */
    const ENABLED_EXTENSIONS = 'enabledExtension';
    /**
     * @var  $avatars
     */
    private $avatars;

    /**
     * Avatars constructor.
     *
     * @param $avatars
     */
    public function __construct($avatars)
    {
        $this->avatars = $avatars;
    }

    /**
     * @return mixed
     */
    public function getDir()
    {
        return $this->avatars[self::DIR];
    }

    /**
     * @return mixed
     */
    public function getMaxHeight()
    {
        return $this->avatars[self::MAX_HEIGHT];
    }

    /**
     * @return mixed
     */
    public function getMaxWidth()
    {
        return $this->avatars[self::MAX_WIDTH];
    }

    /**
     * @return mixed
     */
    public function getMaxFileSize()
    {
        return $this->avatars[self::MAX_FILE_SIZE];
    }

    /**
     * @return mixed
     */
    public function getEnabledExtensions()
    {
        return $this->avatars[self::ENABLED_EXTENSIONS];
    }
    
    /**
     *
     * @return SplFileInfo
     */
    public function getSPLDir()
    {
        return new SplFileInfo($this->getDir());
    }

    /**
     * @return int
     */
    public function getDirSize()
    {
        $size = 0;
        $extensions = [];
        
        foreach ($this->getEnabledExtensions() as $extension) {
            $extensions[] = '*.'.$extension;
        }
        
        foreach (Finder::findFiles($extensions)->in($this->getDir()) as $file) {
            /**
             * @var SplFileInfo $file
             */
            $size += $file->getSize();
        }
        
        return $size;
    }

    /**
     * @return int
     */
    public function getCountOfAvatars()
    {
        $extensions = [];
        
        foreach ($this->getEnabledExtensions() as $ext) {
            $extensions[] = '*.'.$ext;
        }
        
        return count(Finder::findFiles($extensions)->in($this->getDir()));
    }
}
