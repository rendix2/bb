<?php

namespace App\ForumModule\Presenters\Base;

use App\Authorizator;
use App\Controls\BootstrapForm;
use App\Models\Manager;
use App\Presenters\Base\ManagerPresenter;
use Nette\Localization\ITranslator;

/**
 * Description of ForumPresenter
 *
 * @author rendix2
 */
abstract class ForumPresenter extends ManagerPresenter
{
    /**
     * Translator
     *
     * @var ITranslator $forumTranslator
     */
    private $forumTranslator;

    /**
     * @var Authorizator $authorizator
     * @inject
     */
    public $authorizator;
    
    /**
     * @var \App\Models\PmManager $pmManager
     * @inject
     */
    public $pmManager;

    /**
     * ForumPresenter constructor.
     *
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        parent::__construct($manager);
    }

    /**
     * @return ITranslator
     */
    public function getForumTranslator()
    {
        return $this->forumTranslator;
    }
    
    /**
     *
     * @return BootstrapForm
     */
    public function createBootstrapForm()
    {
        $bf = BootstrapForm::create();
        $bf->setTranslator($this->getForumTranslator());
        
        return $bf;
    }

    /**
     *
     * @return BootstrapForm
     */
    public function getBootstrapForm()
    {
        $bf = parent::getBootstrapForm();
        $bf->setTranslator($this->getForumTranslator());
        
        return $bf;
    }

    /**
     * @param $element
     */
    public function checkRequirements($element)
    {
        $user = $this->getUser();
        
        $user->getStorage()->setNamespace(self::FRONT_END_NAMESPACE);
        
        parent::checkRequirements($element);
    }

    /**
     *
     */
    public function startup()
    {
        parent::startup();
        
        $this->forumTranslator = $this->translatorFactory->forumTranslatorFactory();
        $this->getUser()->setAuthorizator($this->authorizator->getAcl());
        
        \Tracy\Debugger::barDump($this->user->isAllowed('1', 'forum_view'));
        
        $this->template->pm_count = $this->pmManager->getCountSent();
    }

    /**
     *
     */
    public function beforeRender()
    {
        parent::beforeRender();

        $this->template->setTranslator($this->forumTranslator);
    }
}
