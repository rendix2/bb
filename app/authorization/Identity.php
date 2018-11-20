<?php

namespace App\Authorization;

/**
 * Description of Identity
 *
 * @author rendix2
 */
class Identity
{
    const ROLE_HOST = 'host';
    
    const ROLE_REGISTERED = 'registered';
    
    const ROLE_ADMIN = 'admin';
    
    private $id;
    
    private $roles;

    /**
     *
     * @param int $id
     * @param array $roles
     */
    public function __construct($id, $roles)
    {
        $this->id     = $id;
        $this->roles = $roles;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }
}
