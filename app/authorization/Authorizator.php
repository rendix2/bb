<?php

namespace App\Authorization;

use Nette\Security\IAuthorizator;

/**
 * Description of Authorizator
 *
 * @author rendix2
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
     * @param Identity            $identity
     * @param IAuthorizationScope $scope
     *
     * @return \Generator
     */
    private function getRoles(Identity $identity, IAuthorizationScope $scope)
    {
        //\Tracy\Debugger::barDump($identity->getRoles(), '$identity->getRoles()');
        //\Tracy\Debugger::barDump($scope->getIdentityRoles($identity), '$scope->getIdentityRoles($identity)');
        
        //globální role
        foreach ($identity->getRoles() as $role) {
           // yield $role; //yield používám, jelikoz když to matchne globální roli, je zbytečné se ptát scope na dynamické role
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
            //\Tracy\Debugger::barDump($this->authorizator->isAllowed($role, $resource, $privilege), '$this->authorizator->isAllowed($role, $resource, $privilege)');
            \Tracy\Debugger::barDump($role, '$role');
            \Tracy\Debugger::barDump($resource, '$resource');
            \Tracy\Debugger::barDump($privilege, '$privilege');
            \Tracy\Debugger::barDump($this->authorizator->isAllowed($role, $resource, $privilege, '$this->authorizator->isAllowed($role, $resource, $privilege'));
            
            
            if ($this->authorizator->isAllowed($role, $resource, $privilege)) {
                return true;
            }
        }

        return false;
    }
}
