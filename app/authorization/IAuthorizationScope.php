<?php

namespace App\Authorization;

/**
 * Description of IAuthorizationScope
 *
 * @author rendix2
 * @package App\Authorization
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
