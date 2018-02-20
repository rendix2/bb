<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 15. 2. 2018
 * Time: 8:47
 */

namespace App\Models;

use Dibi\Connection;

/**
 * Class Topics2Users
 * @package App\Models
 */
class TopicWatchManager extends MNManager
{
    /**
     * Topics2Users constructor.
     *
     * @param Connection    $dibi
     * @param TopicsManager $left
     * @param UsersManager  $right
     * @param null|string   $tableName
     */
    public function __construct(Connection $dibi, TopicsManager $left, UsersManager $right, $tableName = self::TOPIC_WATCH_TABLE)
    {
        parent::__construct($dibi, $left, $right, $tableName);
    }

}