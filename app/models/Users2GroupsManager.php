<?php

namespace App\Models;

use Dibi\Connection;

/**
 * Description of Group2User
 *
 * @author rendi
 */
class Users2GroupsManager extends MNManager
{

    /**
     * Users2GroupsManager constructor.
     *
     * @param Connection    $dibi
     * @param UsersManager  $left
     * @param GroupsManager $right
     */
    public function __construct(Connection $dibi, UsersManager $left, GroupsManager $right)
    {
        parent::__construct(
            $dibi,
            $left,
            $right
        );
    }
}
