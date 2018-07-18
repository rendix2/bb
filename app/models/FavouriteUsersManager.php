<?php

namespace App\Models;

use Dibi\Connection;

/**
 * Description of FavouriteUsers
 *
 * @author rendi
 */
class FavouriteUsersManager extends MNManager
{
    public function __construct(Connection $dibi, UsersManager $left, UsersManager $right, $tableName = self::FAVOURITE_USERS_TABLE, $leftKey = null, $rightKey = null) {
        $right = clone $right;
        
        parent::__construct($dibi, $left, $right, $tableName, $leftKey, 'favourite_user_id');       
    }
}
