<?php

namespace App\Models;

use App\Models\Crud\CrudManager;
use App\Settings\Avatars;
use Dibi\Connection;
use Dibi\Row;
use Nette\Caching\IStorage;
use Nette\Http\FileUpload;
use Nette\InvalidArgumentException;
use Nette\IOException;
use Nette\Utils\FileSystem;
use Nette\Utils\Random;
use Tracy\Debugger;

/**
 * Description of UserManager
 *
 * @author rendix2
 * @package App\Models
 */
class UsersManager extends CrudManager
{

    public function __construct(
        Connection $dibi,
        IStorage   $storage,
        Avatars    $avatars
    ) {
        parent::__construct($dibi, $storage);
    }

}
