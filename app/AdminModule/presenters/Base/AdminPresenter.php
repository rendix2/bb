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
    private $adminTranslator;
    
    /**
     * AdminPresenter destructor.
     */
    public function __destruct()
    {
        $this->adminTranslator = null;
        
        parent::__destruct();
    }

    /**
     * @param mixed $element
     */
    public function checkRequirements($element)
    {
        $user = $this->user;
        
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
    public function beforeRender()
    {
        parent::beforeRender();

        $this->template->setTranslator($this->adminTranslator);
    }
    
    /**
     * @return ITranslator
     */
    public function getTranslator()
    {
        return $this->adminTranslator;
    }
    
    /**
     *
     * @return BootstrapForm
     */
    public function getBootstrapForm()
    {
        $bf = parent::getBootstrapForm();
        $bf->setTranslator($this->getTranslator());
        
        return $bf;
    }
}
