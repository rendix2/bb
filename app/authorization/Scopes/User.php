<?php

namespace App\Authorization\Scopes;

use App\Authorization\Identity;

/**
 * Description of User
 *
 * @author rendix2
 * @package App\Authorization\Scopes
 */
class User
{
    
    /**
     * @var Identity $identity
     */
    private $identity;
    
    /**
     * User constructor.
     *
     * @param Identity $identity
     */
    public function __construct(Identity $identity)
    {
        $this->identity = $identity;
    }
    
    /**
     * User destructor.
     */
    public function __destruct()
    {
        $this->identity = null;
    }

    /**
     * @return Identity
     */
    public function getIdentity()
    {
        return $this->identity;
    }
}
