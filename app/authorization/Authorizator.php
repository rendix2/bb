<?php

namespace App\Authorization;

/**
 * Description of Authorizator
 *
 * @author rendi
 */
class Authorizator {
    
    /** 
     * @var \Nette\Security\IAuthorizator $authorizator
     */
    private $authorizator;
    
    public function __construct(\Nette\Security\IAuthorizator $authorizator)
    {
        $this->authorizator = $authorizator;
    }
    
    public function isAllowed(\App\Authorization\Identity $identity, \App\Authorization\IAuthorizationScope $scope, $action)
    {
        list($resource, $privilege) = $action;
	
        foreach ($this->getRoles($identity, $scope) as $role) {
            if ($this->authorizator->isAllowed($role, $resource, $privilege)) {
                return TRUE;
            }
        }
	return FALSE;
    }

    private function getRoles(Identity $identity, IAuthorizationScope $scope)
    {
        //globální role
	foreach ($identity->getRoles() as $role) {
            yield $role; //yield používám, jelikoz když to matchne globální roli, je zbytečné se ptát scope na dynamické role
	}
	
        foreach ($scope->getIdentityRoles($identity) as $role) {
            yield $role;
	}
    }
}
