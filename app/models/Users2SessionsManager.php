<?php

namespace App\Models;

use Dibi\Connection;

/**
 * Description of Users2SessionsManager
 *
 * @author rendix2
 */
class Users2SessionsManager extends MNManager
{
    /**
     * Users2SessionsManager constructor.
     *
     * @param Connection      $dibi
     * @param UsersManager    $left
     * @param SessionsManager $right
     */
    public function __construct(Connection $dibi, UsersManager $left, SessionsManager $right)
    {
        parent::__construct($dibi, $left, $right);
    }
}
