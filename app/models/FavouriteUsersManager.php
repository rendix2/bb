<?php

namespace App\Models;

use Dibi\Connection;
use Nette\Caching\IStorage;

/**
 * Description of FavouriteUsers
 *
 * @author rendix2
 * @package App\Models
 */
class FavouriteUsersManager extends MNManager
{
    /**
     * FavouriteUsersManager constructor.
     *
     * @param Connection   $dibi
     * @param IStorage     $storage
     * @param UsersManager $left
     * @param UsersManager $right
     * @param string       $tableName
     * @param null         $leftKey
     * @param null         $rightKey
     */
    public function __construct(
        Connection   $dibi,
        IStorage     $storage,
        UsersManager $left,
        UsersManager $right,
        $tableName = self::FAVOURITE_USERS_TABLE,
        $leftKey   = null,
        $rightKey  = null
    ) {
        $right = clone $right;
        
        parent::__construct($dibi, $storage, $left, $right, $tableName, $leftKey, 'favourite_user_id');
    }
}
