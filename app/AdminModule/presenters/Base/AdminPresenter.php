<?php

namespace App\AdminModule\Presenters\Base;

use App\Controls\BootstrapForm;
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
    public function checkRequirements($element): void
    {
        $user = $this->getUser();
        
        $user->getStorage()->setNamespace(self::BECK_END_NAMESPACE);
        
        parent::checkRequirements($element);

        if ($this->name !== 'Login' && !$user->loggedIn) {
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

        $this->template->setTranslator($this->adminTranslator);
    }
    
    public function getTranslator(): ITranslator
    {
        return $this->adminTranslator;
    }
    
    public function getBootstrapForm(): BootstrapForm
    {
        $bf = parent::getBootstrapForm();
        $bf->setTranslator($this->getTranslator());
        
        return $bf;
    }
}
