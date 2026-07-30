<?php

namespace App\Authorization;

use App\Authorization\Scopes\CategoryScope;
use App\Authorization\Scopes\User;
use Exception;

/**
 * Description of AuthorizationPresenter
 *
 * @author rendix2
 * @package App\Authorization
 */
trait AuthorizationPresenter
{
    /**
     * @return Scopes\User
     */
    protected function getLoggedInUser()
    {
        $identity = new Identity($this->getUser()->getId(), $this->getUser()->getRoles());

        return new User($identity);
    }

    /**
     * @param $id
     *
     * @return CategoryScope
     */
    protected function loadCategory($id)
    {
        return new CategoryScope();
    }

    /**
     * @param IAuthorizationScope $scope
     * @param array               $action
     *
     * @throws \Exception
     */
    protected function requireAccess(IAuthorizationScope $scope, array $action)
    {
        if (!$this->isAllowed($scope, $action)) {
            throw new Exception();
        }
    }

    /**
     * @param IAuthorizationScope $scope
     * @param array               $action
     * @return mixed
     */
    protected function isAllowed(IAuthorizationScope $scope, array $action)
    {
        return $this->authorizator->isAllowed($this->getLoggedInUser()->getIdentity(), $scope, $action);
    }
}
