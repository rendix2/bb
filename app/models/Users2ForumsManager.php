<?php

namespace App\Models;

use Dibi\Connection;
use Nette\Caching\IStorage;

/**
 * Description of Users2Forums
 *
 * @author rendix2
 * @package App\Models
 */
class Users2ForumsManager extends MNManager
{
    public function __construct(Connection $dibi, IStorage $storage, UsersManager $left, ForumManager $right)
    {
        parent::__construct($dibi, $storage, $left, $right, 'user_forum');
    }
}
