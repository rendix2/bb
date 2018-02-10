<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

/**
 * Description of Users2Roles
 *
 * @author rendi
 */
class Users2Roles extends MNManager {
    //put your code here
    
    public function __construct(\Dibi\Connection $dibi, \App\Models\UsersManager $left, \App\Models\RolesManager $right) {
        parent::__construct($dibi, $left, $right);
    }
}
