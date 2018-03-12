<?php

namespace App\Controls;

/**
 * Description of Avatars
 *
 * @author rendi
 */
class Avatars {
    
    const DIR = 'dir';
    
    const MAX_HEIGHT = 'maxHeight';
    
    const MAX_WIDTH = 'maxWidth';
    
    const MAX_FILE_SIZE = 'maxFileSize';
    
    const ENABLED_EXTENSIONS = 'enabledExtension';
    
    private $avatars;
    
    public function __construct($avatars)
    {
        $this->avatars = $avatars;
    }
    
    public function getDir()
    {
        return $this->avatars[self::DIR];
    }
    
    public function getMaxHeight()
    {
        return $this->avatars[self::MAX_HEIGHT];
    }
      
    public function getMaxWidth()
    {
        return $this->avatars[self::MAX_WIDTH];
    }
          
    public function getMaxFileSize()
    {
        return $this->avatars[self::MAX_FILE_SIZE];
    }
    
    public function getEnabledExtensions()
    {
        return $this->avatars[self::ENABLED_EXTENSIONS];
    } 
    
    public function getDirSize(){
        $size = 0;               
        $exts = [];
        
        foreach ( $this->getEnabledExtensions() as $ext ){
            $exts[] = '*.'.$ext;
        }
        
        foreach (\Nette\Utils\Finder::findFiles($exts)->in($this->getDir()) as $file) {
            $size += $file->getSize();          
        }
        
        return $size;
    }
    
    public function getCountOfAvatars(){
        $exts = [];
        
        foreach ( $this->getEnabledExtensions() as $ext ){
            $exts[] = '*.'.$ext;
        }        
        
        return count(\Nette\Utils\Finder::findFiles($exts)->in($this->getDir()));
    }
}
