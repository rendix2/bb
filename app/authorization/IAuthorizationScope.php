<?php

namespace App\Authorization;

/**
 * Description of IAuthorizationScope
 *
 * @author rendi
 */
interface IAuthorizationScope
{
    
    public function getIdentityRoles(Identity $identity);
    
}
