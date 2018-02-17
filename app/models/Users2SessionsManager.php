<?php

namespace App\Models;

/**
 * Description of Users2SessionsManager
 *
 * @author rendi
 */
class Users2SessionsManager extends MNManager {
    
    public function __construct(\Dibi\Connection $dibi, UsersManager $left, SessionsManager $right) {
        parent::__construct($dibi, $left, $right);
    }
    
}
