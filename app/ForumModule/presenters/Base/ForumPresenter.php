<?php

namespace App\ForumModule\Presenters\Base;

use App\Authorizator;
use App\Controls\BootstrapForm;
use App\Models\Manager;
use App\Presenters\Base\ManagerPresenter;
use App\Translator;
use Nette\Localization\ITranslator;
use Nette\Security\IAuthorizator;

/**
 * Description of ForumPresenter
 *
 * @author rendi
 */
abstract class ForumPresenter extends ManagerPresenter
{

    /**
     *
     * @var ITranslator $forumTranslator
     */
    private $forumTranslator;

    /**
     * @var BootstrapForm $bootStrapForm
     */
    private $bootStrapForm;

    /**
     * @var IAuthorizator $authorizator
     */
    private $authorizator;
    
    private $appDir;

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
    public function getBootStrapForm()
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
     * @param Authorizator $authorizator
     */
    public function injectAuthorizator(Authorizator $authorizator)
    {
        $this->authorizator = $authorizator;
    }
    
    public function injectAppDir(\App\Controls\AppDir $appDir){
        $this->appDir = $appDir;
    }

    public function startup()
    {
        parent::startup();

        $this->forumTranslator = new Translator($this->appDir,'Forum', $this->getUser()
                                                                   ->getIdentity()
                                                                   ->getData()['lang_file_name']);
        $this->getUser()->setAuthorizator($this->authorizator->getAcl());              
    }

    public function beforeRender()
    {
        parent::beforeRender();

        $this->template->setTranslator($this->forumTranslator);
    }

}
