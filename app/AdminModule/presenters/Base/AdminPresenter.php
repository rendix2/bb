<?php

namespace App\AdminModule\Presenters\Base;

use App\Presenters\crud\CrudPresenter;
use Nette\Localization\ITranslator;

/**
 * Description of AdminPresenter¨
 *
 * @author rendix2
 * @package App\AdminModule\Presenters\Base
 */
abstract class AdminPresenter extends CrudPresenter
{
    /**
     * @var ITranslator $adminTranslator
     */
    private ITranslator $adminTranslator;


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

    /**
     * AdminPresenter startup.
     */
    public function startup()
    {
        parent::startup();

        $this->adminTranslator = $this->translatorFactory->getAdminTranslator();
    }

    /**
     * AdminPresenter beforeRender.
     */
    public function beforeRender(): void
    {
        parent::beforeRender();

        $this->getTemplate()->setTranslator($this->adminTranslator);
    }
    
    public function getTranslator(): ITranslator
    {
        return $this->adminTranslator;
    }
}
