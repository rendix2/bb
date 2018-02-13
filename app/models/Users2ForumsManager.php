<?php

namespace App\Models;

use Dibi\Connection;

/**
 * Description of Users2Forums
 *
 * @author rendi
 */
class Users2ForumsManager extends MNManager{

    public function __construct(Connection $dibi, UsersManager $left, ForumsManager $right) {
        parent::__construct($dibi, $left, $right);
    }
    
}
