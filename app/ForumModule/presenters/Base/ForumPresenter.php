<?php

namespace App\ForumModule\Presenters\Base;

use App\Authorizator;
use App\Controls\BootstrapForm;
use App\Models\Manager;
use App\Models\PmManager;
use App\Presenters\Base\AuthenticatedPresenter;
use Nette\Localization\ITranslator;

/**
 * Description of ForumPresenter
 *
 * @author rendix2
 */
abstract class ForumPresenter extends AuthenticatedPresenter
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
     * @var PmManager $pmManager
     * @inject
     */
    public $pmManager;

    /**
     * @var Manager $manager
     */
    private $manager;

    /**
     * ForumPresenter constructor.
     *
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        parent::__construct();
        
        $this->manager = $manager;
    }
    
    public function __destruct() {
        $this->forumTranslator = null;
        $this->authorizator    = null;
        $this->pmManager       = null;
        $this->manager         = null;
        
        parent::__destruct();
    }

    /**
     *
     * @return Manager
     */
    public function getManager()
    {
        return $this->manager;
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
