<?php

namespace App\AdminModule\Presenters\Base;

use App\Controls\BootstrapForm;
use App\Models\TopicFacade;
use App\Presenters\crud\CrudPresenter;
use Nette\Localization\ITranslator;

/**
 * Description of AdminPresenter¨
 *
 * @author rendix2
 */
abstract class AdminPresenter extends CrudPresenter
{
    /**
     * @var ITranslator $adminTranslator
     */
    private $adminTranslator;
    
    /**
     *
     * @var TopicFacade $topicFacade
     * @inject
    */
    public $topicFacade;
    
    public function __destruct()
    {
        $this->topicFacade     = null;
        $this->adminTranslator = null;
        
        parent::__destruct();
    }

    /**
     * @param $element
     */
    public function checkRequirements($element)
    {
        $this->getUser()->getStorage()->setNamespace(self::BECK_END_NAMESPACE);
        
        parent::checkRequirements($element);

        if ($this->getName() !== 'Login' && !$this->getUser()->isLoggedIn()) {
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
