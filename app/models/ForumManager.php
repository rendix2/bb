<?php

namespace App\Models;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\Models\Crud\CrudManager;
use Dibi\Connection;
use Dibi\DriverException;
use Nette\Caching\IStorage;

/**
 * Description of ForumManager
 *
 * @author rendix2
 * @package App\Models
 */
#[\JetBrains\PhpStorm\Deprecated]
class ForumManager extends CrudManager
{
    public function __construct(
        Connection $dibi,
        IStorage $storage
    )
    {
        parent::__construct($dibi, $storage);
    }
}
