<?php declare(strict_types=1);

namespace App\services;

use App\Authorization\Authorizator;
use App\Authorization\IAuthorizationScope;
use App\Authorization\Identity;
use App\Authorization\Scopes\User;

class CheckPermissionService
{
    public function __construct(
        private readonly Authorizator         $authorizator,
        private readonly \Nette\Security\User $user,
    )
    {
    }


    public function requireAccess(IAuthorizationScope $scope, array $action): void
    {
        if (!$this->isAllowed($scope, $action)) {
            throw new Exception();
        }
    }

    public function isAllowed(IAuthorizationScope $scope, array $action): bool
    {
        return $this->authorizator->isAllowed($this->getLoggedInUser()->getIdentity(), $scope, $action);
    }

    private function getLoggedInUser(): User
    {
        $identity = new Identity($this->user->getId(), $this->user->getRoles());

        return new User($identity);
    }

}