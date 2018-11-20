<?php

namespace App\Authorization\Scopes;

use App\Authorization\Identity;

/**
 * Description of User
 *
 * @author rendix2
 */
class User
{
    /**
     *
     * @param Identity $identity
     */
    public function __construct(Identity $identity)
    {
        $this->identity = $identity;
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
