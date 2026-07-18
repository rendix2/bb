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
    /**
     * Users2GroupsManager constructor.
     *
     * @param Connection    $dibi
     * @param IStorage      $storage
     * @param UsersManager  $left
     * @param GroupManager $right
     */
    public function __construct(Connection $dibi, IStorage $storage, UsersManager $left, GroupManager $right)
    {
        parent::__construct($dibi, $storage,$left, $right, 'user_group');
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

    /**
     * @param int $user_id
     * @param int $forum_id
     *
     * @return Row[]
     */
    public function getForumsPermissionsByUserThroughGroupAndForum($user_id, $forum_id)
    {
        return $this->getAllFluent()
                ->as('ug')
                ->innerJoin(self::FORUMS2GROUPS_TABLE)
                ->as('fg')
                ->on('[ug.group_id] = [fg.group_id]')
                ->where('[ug.user_id] = %i', $user_id)
                ->where('[fg.forum_id] = %i', $forum_id)
                ->fetchAll();
    }
}
