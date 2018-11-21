<?php

namespace App\Models\Entity;

use App\Models\Entity\Base\Entity;
use Dibi\Row;
use Nette\Utils\ArrayHash;

/**
 * Description of FileEntity
 *
 * @author rendix2
 * @package App\Models\Entity
 */
class FileEntity extends Entity
{
    /**
     *
     * @var int $file_id
     */
    private $file_id;
 
    /**
     *
     * @var string $file_orig_name
     * 
     */
    private $file_orig_name;

    /**
     *
     * @var string $file_name
     */
    private $file_name;
    
    /**
     *
     * @var string $file_extension
     */
    private $file_extension;
    
    /**
     *
     * @var int $file_size
     */
    private $file_size;

    public function getFile_id()
    {
        return $this->file_id;
    }

    public function getFile_orig_name()
    {
        return $this->file_orig_name;
    }
        
    public function getFile_name()
    {
        return $this->file_name;
    }

    public function getFile_extension()
    {
        return $this->file_extension;
    }

    public function getFile_size()
    {
        return $this->file_size;
    }

    public function setFile_id($file_id)
    {
        $this->file_id = $file_id;
        return $this;
    }
    
    public function setFile_orig_name($file_orig_name)
    {
        $this->file_orig_name = $file_orig_name;
        return $this;
    }

    public function setFile_name($file_name)
    {
        $this->file_name = $file_name;
        return $this;
    }

    public function setFile_extension($file_extension)
    {
        $this->file_extension = $file_extension;
        return $this;
    }

    public function setFile_size($file_size)
    {
        $this->file_size = $file_size;
        return $this;
    }

    /**
     * 
     * @return array
     */
    public function getArray()
    {
        $res = [];
        
        if (isset($this->file_id)) {
            $res['file_id'] = $this->file_id;
        }
        
        if (isset($this->file_orig_name)) {
            $res['file_orig_name'] = $this->file_orig_name;
        }        
        
        if (isset($this->file_name)) {
            $res['file_name'] = $this->file_name;
        }

        if (isset($this->file_extension)) {
            $res['file_extension'] = $this->file_extension;
        }

        if (isset($this->file_size)) {
            $res['file_size'] = $this->file_size;
        }  
        
        return $res;
    }
    
    /**
     * 
     * @param ArrayHash $values
     * 
     * @return FileEntity
     */
    public static function setFromArrayHash(ArrayHash $values)
    {
        $fileEntity = new FileEntity();
        
        if (isset($values->file_id)) {
            $fileEntity->setFile_id($values->file_id);
        }
        
        if (isset($values->file_orig_name)) {
            $fileEntity->setFile_orig_name($values->file_orig_name);
        }
        
        if (isset($values->file_name)) {
            $fileEntity->setFile_name($values->file_name);
        }

        if (isset($values->file_extension)) {
            $fileEntity->setFile_extension($values->file_extension);
        }

        if (isset($values->file_size)) {
            $fileEntity->setFile_size($values->file_size);
        }      
        
        return $fileEntity;
    }
    
    /**
     * 
     * @param Row $values
     * 
     * @return FileEntity
     */
    public static function setFromRow(Row $values)
    {
        $fileEntity = new FileEntity();
        
        if (isset($values->file_id)) {
            $fileEntity->setFile_id($values->file_id);
        }
        
        if (isset($this->file_orig_name)) {
            $fileEntity->setFile_orig_name($values->file_orig_name);
        }        
        
        if (isset($values->file_name)) {
            $fileEntity->setFile_name($values->file_name);
        }

        if (isset($values->file_extension)) {
            $fileEntity->setFile_extension($values->file_extension);
        }

        if (isset($values->file_size)) {
            $fileEntity->setFile_size($values->file_size);
        }      
        
        return $fileEntity;
    }
}
