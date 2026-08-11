<?php

namespace App\Models;

use App\Models\Crud\CrudManager;
use Dibi\Connection;
use Nette\Caching\IStorage;

class UsersManager extends CrudManager
{

    public function __construct(
        Connection $dibi,
        IStorage   $storage,
    ) {
        parent::__construct($dibi, $storage);
    }

}
