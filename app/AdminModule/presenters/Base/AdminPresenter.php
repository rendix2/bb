<?php

namespace App\AdminModule\Presenters\Base;

use App\Controls\BootstrapForm;
use App\Models\Crud\CrudManager;
use App\Presenters\crud\CrudPresenter;
use Nette\Localization\ITranslator;

/**
 * Description of AdminPresenter¨
 *
 * @author rendi
 */
abstract class AdminPresenter extends CrudPresenter
{
    /**
     * @var ITranslator $adminTranslator
     */
    private $adminTranslator;

    /**
     * @param $element
     */
    public function checkRequirements($element)
    {
        $this->getUser()->getStorage()->setNamespace(self::BECK_END_NAMESPACE);
        
        parent::checkRequirements($element);

        if (!$this->getUser()->isLoggedIn() && $this->getName() !== 'Login') {
            $this->redirect(':Admin:Login:default');
        }

        if (!$this->getUser()->isInRole('admin')) {
            $this->error('You are not admin.');
        }
    }

    /**
     *
     */
    public function startup()
    {
        parent::startup();

        $this->adminTranslator = $this->translatorFactory->adminTranslatorFactory();
    }

    /**
     *
     */
    public function beforeRender()
    {
        parent::beforeRender();

        $this->template->setTranslator($this->adminTranslator);
    }
    
    /**
     * @return ITranslator
     */
    public function getAdminTranslator()
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
        $bf->setTranslator($this->getAdminTranslator());
        
        return $bf;
    }

    /**
     *
     * @return BootstrapForm
     */
    public function createBootstrapForm()
    {
        $bf = BootstrapForm::create();
        $bf->setTranslator($this->getAdminTranslator());
        
        return $bf;
    }
}
