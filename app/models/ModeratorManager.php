<?php

namespace App\Models;

use Dibi\Connection;
use Nette\Caching\IStorage;

/**
 * Description of ModeratorsManager
 *
 * @author rendix2
 * @package App\Models
 */
class ModeratorManager extends MNManager
{
    /**
     * ModeratorsManager constructor.
     *
     * @param Connection    $dibi
     * @param IStorage      $storage
     * @param UsersManager  $left
     * @param ForumManager $right
     * @param string        $tableName
     */
    public function __construct(
        Connection    $dibi,
        IStorage $storage,
        UsersManager  $left,
        ForumManager $right,
        $tableName = self::MODERATORS_TABLE
    ) {
        parent::__construct($dibi, $storage, $left, $right, $tableName);
    }
}
