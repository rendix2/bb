<?php

namespace App\Models;

use Dibi\Connection;

/**
 * Description of Forums2Groups
 *
 * @author rendix2
 * @package App\Models
 */
class Forums2GroupsManager extends MNManager
{

    /**
     * Forums2GroupsManager constructor.
     *
     * @param Connection    $dibi
     * @param ForumsManager $left
     * @param GroupsManager $right
     */
    public function __construct(Connection $dibi, ForumsManager $left, GroupsManager $right)
    {
        parent::__construct($dibi, $left, $right);
    }

    /**
     * @param int   $group_id
     * @param array $data
     */
    public function addForums2group($group_id, array $data)
    {
        $this->deleteByRight($group_id);
        $this->addNative($data);
    }
}
