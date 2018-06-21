<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

/**
 * Description of ModeratorsManager
 *
 * @author rendi
 */
class ModeratorsManager extends MNManager
{
    /**
     * ModeratorsManager constructor.
     *
     * @param \Dibi\Connection $dibi
     * @param UsersManager     $left
     * @param ForumsManager    $right
     * @param string           $tableName
     */
    public function __construct(\Dibi\Connection $dibi, UsersManager $left, ForumsManager $right, $tableName = 'moderators') {
        parent::__construct($dibi, $left, $right, $tableName);
    }
}
