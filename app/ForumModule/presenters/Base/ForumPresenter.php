<?php

namespace App\ForumModule\Presenters\Base;

use App\Authorizator;
use App\Controls\AppDir;
use App\Controls\BootstrapForm;
use App\Models\Manager;
use App\Presenters\Base\ManagerPresenter;
use App\Translator;
use Nette\Localization\ITranslator;

/**
 * Description of ForumPresenter
 *
 * @author rendi
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
     * Bootstrap form
     *
     * @var BootstrapForm $bootStrapForm
     */
    public $bootStrapForm;

    /**
     * @var Authorizator $authorizator
     * @inject
     */
    public $authorizator;
    
    /**
     * @var \App\Services\TranslatorFactory $translatorFactory
     * @inject
     */
    public $translatorFactory;

    /**
     * ForumPresenter constructor.
     *
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        parent::__construct($manager);

        $this->bootStrapForm = new BootstrapForm();
    }

    /**
     *
     * @return BootstrapForm
     */
    public function getBootstrapForm()
    {
        return $this->bootStrapForm;
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
     */
    public function startup()
    {
        parent::startup();
        
        $this->forumTranslator = $this->translatorFactory->forumTranslatorFactory();
        $this->getUser()->setAuthorizator($this->authorizator->getAcl());
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
