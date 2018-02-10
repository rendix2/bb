<?php

namespace App\Models;

/**
 * Description of Users2Roles
 *
 * @author rendi
 */
class Users2Roles extends MNManager {
    //put your code here
    
    public function __construct(\Dibi\Connection $dibi, \App\Models\RolesManager $left, \App\Models\UsersManager $right) {
        parent::__construct($dibi, $left, $right);
    }
}
