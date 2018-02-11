<?php

namespace App\Models;

/**
 * Description of Users2Forums
 *
 * @author rendi
 */
class Users2ForumsManager extends MNManager{

    public function __construct(\Dibi\Connection $dibi, UsersManager $left, ForumsManager $right) {
        parent::__construct($dibi, $left, $right);
    }
    
}
