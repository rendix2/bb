<?php

namespace App\Models\Entity;

use App\Models\Entity\Base\Entity;
use Dibi\Row;
use Nette\Utils\ArrayHash;

/**
 * Description of File
 *
 * @author rendi
 */
class File extends Entity
{
    /**
     *
     * @var int $file_id
     */
    private $file_id;
    
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

    /**
     * @return int
     */
    public function getFile_id()
    {
        return $this->file_id;
    }

    /**
     * @return string
     */
    public function getFile_name()
    {
        return $this->file_name;
    }

    /**
     * @return string
     */
    public function getFile_extension()
    {
        return $this->file_extension;
    }

    /**
     * @return int
     */
    public function getFile_size()
    {
        return $this->file_size;
    }

    /**
     * @param $file_id
     * @return $this
     */
    public function setFile_id($file_id)
    {
        $this->file_id = $file_id;
        return $this;
    }

    /**
     * @param $file_name
     * @return $this
     */
    public function setFile_name($file_name)
    {
        $this->file_name = $file_name;
        return $this;
    }

    /**
     * @param $file_extension
     * @return $this
     */
    public function setFile_extension($file_extension)
    {
        $this->file_extension = $file_extension;
        return $this;
    }

    /**
     * @param $file_size
     * @return $this
     */
    public function setFile_size($file_size)
    {
        $this->file_size = $file_size;
        return $this;
    }

    /**
     * @return array
     */
    public function getArray()
    {
        $res = [];
        
        if (isset($this->file_id)) {
            $res['file_id'] = $this->file_id;
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
     * @param ArrayHash $values
     * @return File
     */
    public static function setFromArrayHash(ArrayHash $values)
    {
        $file = new File();
        
        if (isset($values->file_id)) {
            $file->setFile_id($values->file_id);
        }
        
        if (isset($values->file_name)) {
            $file->setFile_name($values->file_name);
        }

        if (isset($values->file_extension)) {
            $file->setFile_extension($values->file_extension);
        }

        if (isset($values->file_size)) {
            $file->setFile_size($values->file_size);
        }
        
        return $file;
    }

    /**
     * @param Row $values
     * @return File
     */
    public static function setFromRow(Row $values)
    {
        $file = new File();
        
        if (isset($values->file_id)) {
            $file->setFile_id($values->file_id);
        }
        
        if (isset($values->file_name)) {
            $file->setFile_name($values->file_name);
        }

        if (isset($values->file_extension)) {
            $file->setFile_extension($values->file_extension);
        }

        if (isset($values->file_size)) {
            $file->setFile_size($values->file_size);
        }

        return $file;
    }
}
