<?php

namespace App\Authorization\Scopes;

use App\Authorization\IAuthorizationScope;
use App\Authorization\Identity;

/**
 * Description of Forum
 *
 * @author rendi
 */
class Forum implements IAuthorizationScope {

    const ROLE_MODERATOR = 'Forum:manager';
    
    /**
     * @var int $id;
     */
    private $id;

    /**
     * @param Identity $identity
     *
     * @return array
     */
    public function getIdentityRoles(Identity $identity)
    {
        return [];
    }
}
