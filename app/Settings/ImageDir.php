<?php

namespace App\Settings;

use Nette\Utils\Finder;
use SplFileInfo;

/**
 * Description of ImageDir
 *
 * @author rendix2
 * @package App\Settings
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
     * ImageDir constructor.
     *
     * @param array $dir
     */
    public function __construct(array $dir)
    {
        $this->dir = $dir;

        /*
        if (!file_exists($this->dir[self::DIR]) && !is_dir($this->dir[self::DIR])) {
            mkdir($this->dir[self::DIR]);
        }
        */
    }

    public function getDir(): string
    {
        return $this->dir[self::DIR];
    }
    
    public function getTemplateDir(): string
    {
        return $this->dir[self::RELATIVE_DIR];
    }

    public function getMaxHeight(): int
    {
        return $this->dir[self::MAX_HEIGHT];
    }

    public function getMaxWidth(): int
    {
        return $this->dir[self::MAX_WIDTH];
    }

    public function getMaxFileSize(): int
    {
        return $this->dir[self::MAX_FILE_SIZE];
    }

    public function getEnabledExtensions(): array
    {
        return $this->dir[self::ENABLED_EXTENSIONS];
    }
    
    public function getSPLDir(): SplFileInfo
    {
        return new SplFileInfo($this->getDir());
    }

    public function getDirSize(): int
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

    public function getImageCount(): int
    {
        $extensions = [];
        
        foreach ($this->getEnabledExtensions() as $ext) {
            $extensions[] = '*.'.$ext;
        }
        
        return count(Finder::findFiles($extensions)->in($this->getDir()));
    }
}
