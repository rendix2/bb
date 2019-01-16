<?php

namespace App\Models;

use Dibi\Connection;
use Nette\Caching\IStorage;

/**
 * Description of Users2SessionsManager
 *
 * @author rendix2
 * @package App\Models
 */
class Users2SessionsManager extends MNManager
{
    /**
     * Users2SessionsManager constructor.
     *
     * @param Connection      $dibi
     * @param IStorage        $storage
     * @param UsersManager    $left
     * @param SessionsManager $right
     */
    public function __construct(Connection $dibi, IStorage $storage, UsersManager $left, SessionsManager $right)
    {
        parent::__construct($dibi, $storage, $left, $right);
    }
}
