<?php

namespace App\Authorization;

/**
 * Description of Identity
 *
 * @author rendi
 */
class Identity
{

    const ROLE_ADMIN = 'admin';
    
    private $id;
    
    private $roles = [];

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }
}
