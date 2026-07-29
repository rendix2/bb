<?php

namespace App\Models;

use App\Models\Crud\CrudManager;
use App\Settings\Ranks;
use Dibi\Connection;
use Nette\Caching\IStorage;
use Nette\Http\FileUpload;
use Nette\IOException;
use Nette\Utils\FileSystem;
use Nette\Utils\Random;
use Tracy\Debugger;

/**
 * Description of RanksManager
 *
 * @author rendix2
 * @package App\Models
 */
class RankManager extends CrudManager
{
    /**
     * @var int
     */
    const NOT_UPLOADED = -5;


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

    }
}
