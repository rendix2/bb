<?php

namespace App\AdminModule\Presenters\Base;

use App\Presenters\Base\AuthenticatedPresenter;

/**
 * Description of AdminPresenter¨
 *
 * @author rendix2
 * @package App\AdminModule\Presenters\Base
 */
abstract class AdminPresenter extends AuthenticatedPresenter
{
    /**
     * @param mixed $element
     */
    public function checkRequirements(\ReflectionClass|\ReflectionMethod $element): void
    {
        $user = $this->getUser();
        
        $user->getStorage()->setNamespace(self::BACK_END_NAMESPACE);
        
        parent::checkRequirements($element);

        if ($this->getName() !== 'Login' && !$user->isLoggedIn()) {
            $this->redirect(':Admin:Login:default');
        }

        if (!$user->isInRole('admin')) {
            $this->error('You are not admin.');
        }
    }
}
