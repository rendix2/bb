<?php

namespace App\Authorization;

use Generator;
use Nette\Security\IAuthorizator;

/**
 * Description of Authorizator
 *
 * @author rendix2
 * @package App\Authorization
 */
class Authorizator
{
    /**
     * @var string
     */
    const ROLES = [ 1 => 'guest', 2 => 'registered', 3 => 'moderator', 4 => 'juniorAdmin', 5 => 'admin'];
    
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
     *
     */
    public function __destruct()
    {
        $this->authorizator = null;
    }

    /**
     * @param Identity            $identity
     * @param IAuthorizationScope $scope
     *
     * @return Generator
     */
    private function getRoles(Identity $identity, IAuthorizationScope $scope)
    {
        // global roles
        foreach ($identity->getRoles() as $role) {
            yield $role;
        }

        foreach ($scope->getIdentityRoles($identity) as $role) {
            yield $role;
        }
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
}
