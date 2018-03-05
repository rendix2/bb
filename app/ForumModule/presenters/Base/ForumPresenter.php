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
    private $bootStrapForm;

    /**
     * @var Authorizator $authorizator
     */
    private $authorizator;

    /**
     * @var AppDir $appDir
     */
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
     * @param AppDir $appDir
     */
    public function injectAppDir(AppDir $appDir)
    {
        $this->appDir = $appDir;
    }

    /**
     * @param Authorizator $authorizator
     */
    public function injectAuthorizator(Authorizator $authorizator)
    {
        $this->authorizator = $authorizator;
    }

    /**
     *
     */
    public function startup()
    {
        parent::startup();

        $this->forumTranslator = new Translator(
            $this->appDir,
            'Forum',
            $this->getUser()
                ->getIdentity()
                ->getData()['lang_file_name']
        );
        $this->getUser()
            ->setAuthorizator($this->authorizator->getAcl());
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
