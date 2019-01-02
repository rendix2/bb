<?php

namespace App\Models;

use App\Models\Crud\CrudManager;
use App\Settings\Ranks;
use Dibi\Connection;
use Nette\Caching\IStorage;
use Nette\Http\FileUpload;
use Nette\IOException;
use Nette\Utils\FileSystem;

/**
 * Description of RanksManager
 *
 * @author rendix2
 * @package App\Models
 */
class RanksManager extends CrudManager
{
    /**
     * @var int
     */
    const NOT_UPLOADED = -5;
    
    /**
     * @var Ranks $ranks
     */
    private $ranks;

    /**
     * RanksManager constructor.
     *
     * @param Connection $dibi
     * @param IStorage   $storage
     * @param Ranks      $ranks
     */
    public function __construct(Connection $dibi, IStorage $storage, Ranks $ranks)
    {
        parent::__construct($dibi, $storage);

        $this->ranks = $ranks;
    }
    
    /**
     * RanksManager destructor.
     */
    public function __destruct()
    {
        $this->ranks = null;
        
        parent::__destruct();
    }

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

            if ($rank && $rank->rank_file) {
                $this->removeRankFile($rank->rank_file);
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

    /**
     *
     * @param string $rank_file
     *
     * @return bool
     */
    public function removeRankFile($rank_file)
    {
        try {
            FileSystem::delete($this->ranks->getDir() . DIRECTORY_SEPARATOR . $rank_file);
            
            return true;
        } catch (IOException $e) {
            Debugger::log(sprintf('File %s was not deleted.', $this->ranks->getDir() . DIRECTORY_SEPARATOR . $rank_file));
            return false;
        }
    }
}
