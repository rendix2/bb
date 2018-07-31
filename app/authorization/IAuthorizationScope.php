<?php

namespace App\Authorization;

/**
 * Description of IAuthorizationScope
 *
 * @author rendi
 */
interface IAuthorizationScope
{
    /**
     * @param Identity $identity
     *
     * @return mixed
     */
    public function getIdentityRoles(Identity $identity);
    
}
