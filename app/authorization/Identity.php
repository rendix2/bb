<?php

namespace App\Authorization;

/**
 * Description of Identity
 *
 * @author rendix2
 * @package App\Authorization
 */
class Identity
{
    const ROLE_HOST       = 'host';
    const ROLE_REGISTERED = 'registered';
    const ROLE_ADMIN      = 'admin';
    
    private $id;
    
    private $roles;

    /**
     * Identity constructor.
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
     * Identity destructor.
     */
    public function __destruct()
    {
        $this->id    = null;
        $this->roles = null;
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
