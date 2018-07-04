<?php

namespace App\Authorization\Scopes;

/**
 * Description of Forum
 *
 * @author rendi
 */
class Forum implements \App\Authorization\IAuthorizationScope {

    const ROLE_MODERATOR = 'Forum:manager';
    
    /**
     * @var int $id;
     */
    private $id;
   
    
    public function getIdentityRoles(\App\Authorization\Identity $identity)
    {
        return [];
    }
}
