<?php

namespace App\Models;

use Dibi\Connection;
use Dibi\Row;
use Nette\Caching\IStorage;

/**
 * Description of Group2User
 *
 * @author rendix2
 * @package App\Models
 */
class User2GroupManager extends MNManager
{
    public function __construct(Connection $dibi, IStorage $storage, UsersManager $left, GroupManager $right)
    {
        parent::__construct($dibi, $storage,$left, $right, 'user_group');
    }
}
