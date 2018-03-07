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
     *
     */
    const RANK_FOLDER = 'ranks';

    /**
     *
     */
    const NOT_UPLOADED = -5;

    /**
     * @param int    $id
     * @param string $wwwDir
     */
    public function deletePreviousRankFile($id, $wwwDir)
    {
        $rank      = $this->getById($id);
        $separator = DIRECTORY_SEPARATOR;

        if ($rank) {
            FileSystem::delete($wwwDir . $separator . self::RANK_FOLDER . $separator . $rank->rank_file);
        }
    }

    /**
     * @param FileUpload $file
     * @param int        $id
     * @param string     $wwwDir
     *
     * @return string
     */
    public function moveRank(FileUpload $file, $id, $wwwDir)
    {
        if ($file->ok) {
            $this->deletePreviousRankFile(
                $id,
                $wwwDir
            );

            $extension = self::getFileExtension($file->name);
            $hash      = self::getRandomString();
            $separator = DIRECTORY_SEPARATOR;
            $name      = $hash . '.' . $extension;

            $file->move($wwwDir . $separator . self::RANK_FOLDER . $separator . $name);

            return $name;
        } else {
            return self::NOT_UPLOADED;
        }
    }
}
