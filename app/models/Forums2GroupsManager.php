<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Dibi\Connection;

/**
 * Description of Forums2Groups
 *
 * @author rendi
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
        $this->dibi->query('INSERT INTO [' . $this->getTable() . '] %m', $data);
    }
}
