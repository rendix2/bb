<?php

namespace App\Authorization\Scopes;

use App\Authorization\IAuthorizationScope;
use App\Authorization\Identity;

/**
 * Description of Category
 *
 * @author rendi
 */
class Category implements IAuthorizationScope
{
    /**
     * @param Identity $identity
     */
    public function getIdentityRoles(Identity $identity)
    {
    }
}
