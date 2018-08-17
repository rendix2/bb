<?php

namespace App\Authorization;

/**
 * Description of IAuthorizationScope
 *
 * @author rendix2
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
