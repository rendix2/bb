<?php

namespace App\Settings;

use Nette\Utils\Finder;
use SplFileInfo;

/**
 * Description of ImageDir
 *
 * @author rendix2
 */
abstract class ImageDir
{
    
    /**
     * @var string
     */
    const DIR = 'dir';
    
    /**
     * @var string
     */
    const RELATIVE_DIR = 'relativeDir';
    
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
     * @var array $dir
     */
    private $dir;

    /**
     * Avatars constructor.
     *
     * @param $dir
     */
    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    /**
     * @return array
     */
    public function getDir()
    {
        return $this->dir[self::DIR];
    }
    
    /**
     *
     * @return string
     */
    public function getTemplateDir()
    {
        return $this->dir[self::RELATIVE_DIR];
    }

    /**
     * @return int
     */
    public function getMaxHeight()
    {
        return $this->dir[self::MAX_HEIGHT];
    }

    /**
     * @return int
     */
    public function getMaxWidth()
    {
        return $this->dir[self::MAX_WIDTH];
    }

    /**
     * @return int
     */
    public function getMaxFileSize()
    {
        return $this->dir[self::MAX_FILE_SIZE];
    }

    /**
     * @return string
     */
    public function getEnabledExtensions()
    {
        return $this->dir[self::ENABLED_EXTENSIONS];
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
    public function getImageCount()
    {
        $extensions = [];
        
        foreach ($this->getEnabledExtensions() as $ext) {
            $extensions[] = '*.'.$ext;
        }
        
        return count(Finder::findFiles($extensions)->in($this->getDir()));
    }
    
}
