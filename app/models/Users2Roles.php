<?php

namespace App\Models;

use Dibi\Connection;

/**
 * Description of Users2Roles
 *
 * @author rendi
 */
class Users2Roles extends MNManager {

    /**
     * Users2Roles constructor.
     *
     * @param Connection   $dibi
     * @param RolesManager $left
     * @param UsersManager $right
     */
    public function __construct(Connection $dibi, RolesManager $left, UsersManager $right) {
        parent::__construct($dibi, $left, $right);
    }
}
