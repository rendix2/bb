<?php

namespace App\Models;

/**
 * Description of Group2User
 *
 * @author rendi
 */
class Users2Groups extends MNManager{

    public function __construct(\Dibi\Connection $dibi, UsersManager $left, GroupsManager $right) {
        parent::__construct($dibi, $left, $right);
    }
}
