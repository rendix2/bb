<?php

namespace App\Authorization\Scopes;

use App\Authorization\Identity;

/**
 * Description of User
 *
 * @author rendix2
 */
class User {
    
    public function __construct()
    {
        $this->identity = new Identity();
    }

        /**
     * @var Identity $identity
     */
    private $identity;

    /**
     * @return Identity
     */
    public function getIdentity()
    {
        return $this->identity;
    }
}
