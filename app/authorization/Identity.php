<?php

namespace App\Authorization;

/**
 * Description of Identity
 *
 * @author rendix2
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
