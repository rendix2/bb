<?php

namespace App\Models;

use Dibi\Connection;

/**
 * Description of Group2User
 *
 * @author rendix2
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
        parent::__construct($dibi, $left, $right);
    }
    
    /**
     * @param int $user_id
     *
     * @return Row[]
     */
    public function getForumsPermissionsByUserThroughGroup($user_id)
    {
        return $this->getAllFluent()
                ->as('ug')
                ->innerJoin(self::FORUMS2GROUPS_TABLE)
                ->as('fg')
                ->on('[ug.group_id] = [fg.group_id]')
                ->where('[ug.user_id] = %i', $user_id)
                ->fetchAll();
    }
}
