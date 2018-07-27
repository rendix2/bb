<?php

namespace App\Authorization;

use App\Authorization\IAuthorizationScope;
use App\Authorization\Identity;
use Nette\Security\IAuthorizator;

/**
 * Description of Authorizator
 *
 * @author rendi
 */
class Authorizator {
    
    /**
     * @var IAuthorizator $authorizator
     */
    private $authorizator;

    /**
     * Authorizator constructor.
     *
     * @param IAuthorizator $authorizator
     */
    public function __construct(IAuthorizator $authorizator)
    {
        $this->authorizator = $authorizator;
    }

    /**
     * @param Identity            $identity
     * @param IAuthorizationScope $scope
     * @param                     $action
     *
     * @return bool
     */
    public function isAllowed(Identity $identity, IAuthorizationScope $scope, $action)
    {
        list($resource, $privilege) = $action;

        foreach ($this->getRoles($identity, $scope) as $role) {
            if ($this->authorizator->isAllowed($role, $resource, $privilege)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Identity            $identity
     * @param IAuthorizationScope $scope
     *
     * @return \Generator
     */
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
