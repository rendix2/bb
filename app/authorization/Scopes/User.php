<?php

namespace App\Authorization\Scopes;

/**
 * Description of User
 *
 * @author rendi
 */
class User {
    
    public function __construct() {
        $this->identity = new \App\Authorization\Identity();
    }

        /**
     * @var \App\Authorization\Identity $identity
     */
    private $identity;
    
    public function getIdentity()
    {
        return $this->identity;
    }
}
