<?php

namespace App\Models;

use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;

/**
 * Description of RanksManager
 *
 * @author rendi
 */
class RanksManager extends Crud\CrudManager
{
    /**
     * @var string
     */
    const RANK_FOLDER = 'ranks';

    /**
     * @var int
     */
    const NOT_UPLOADED = -5;
    
    /**
     * @var \App\Controls\Ranks $ranks
     * @inject
     */
    public $ranks;

    /**
     * @param FileUpload $file
     * @param int        $id
     *
     * @return string
     */
    public function moveRank(FileUpload $file, $id)
    {
        if ($file->ok) {
            $rank = $this->getById($id);

            if ($rank) {
                FileSystem::delete($this->ranks->getDir() . DIRECTORY_SEPARATOR . $rank->rank_file);
            }

            $extension = self::getFileExtension($file->name);
            $hash      = self::getRandomString();
            $name      = $hash . '.' . $extension;

            $file->move($this->ranks->getDir() . DIRECTORY_SEPARATOR . $name);

            return $name;
        } else {
            return self::NOT_UPLOADED;
        }
    }
}
