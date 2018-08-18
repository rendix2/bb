<?php

namespace App\Models;

use Dibi\Connection;

/**
 * Description of FavouriteUsers
 *
 * @author rendix2
 */
class FavouriteUsersManager extends MNManager
{
    /**
     * FavouriteUsersManager constructor.
     *
     * @param Connection   $dibi
     * @param UsersManager $left
     * @param UsersManager $right
     * @param string       $tableName
     * @param null         $leftKey
     * @param null         $rightKey
     */
    public function __construct(
        Connection $dibi,
        UsersManager $left,
        UsersManager $right,
        $tableName = self::FAVOURITE_USERS_TABLE,
        $leftKey = null,
        $rightKey = null
    ) {
        $right = clone $right;
        
        parent::__construct($dibi, $left, $right, $tableName, $leftKey, 'favourite_user_id');
    }
}
