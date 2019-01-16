<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 15. 2. 2018
 * Time: 8:47
 */

namespace App\Models;

use Dibi\Connection;
use Nette\Caching\IStorage;

/**
 * Class Topics2Users
 *
 * @author rendix2
 * @package App\Models
 */
class TopicWatchManager extends MNManager
{
    /**
     * TopicWatchManager constructor.
     *
     * @param Connection    $dibi
     * @param IStorage      $storage
     * @param TopicsManager $left
     * @param UsersManager  $right
     * @param string        $tableName
     */
    public function __construct(
        Connection    $dibi,
        IStorage      $storage,
        TopicsManager $left,
        UsersManager  $right,
        $tableName = self::TOPIC_WATCH_TABLE
    ) {
        parent::__construct($dibi, $storage, $left, $right, $tableName);
    }
}
