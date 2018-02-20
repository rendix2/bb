<?php

namespace App\AdminModule\Presenters\Base;

use App\Controls\AppDir;
use App\Controls\BootstrapForm;
use App\Models\Crud\CrudManager;
use App\Presenters\crud\CrudPresenter;
use App\Translator;
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
     * @var BootstrapForm $bootStrapForm
     */
    private $bootStrapForm;

    /**
     * @var
     */
    private $appDir;

    /**
     * AdminPresenter constructor.
     *
     * @param CrudManager $manager
     */
    public function __construct(CrudManager $manager)
    {
        parent::__construct($manager);

        $this->bootStrapForm = new BootstrapForm();
    }

    /**
     * @param AppDir $appDir
     */
    public function injectAppDir(AppDir $appDir){
        $this->appDir = $appDir;
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
    public function getBootStrapForm()
    {
        return $this->bootStrapForm;
    }

    /**
     *
     */
    public function startup()
    {
        parent::startup();

        $user = $this->getUser();

        if (!$user->isLoggedIn()) {
            $this->error('You are not logged in.');
        }

        if (!$user->isInRole('admin')) {
            $this->error('You are not admin.');
        }

        $lang_name = $this->getUser()->getIdentity()->getData()['lang_file_name'];

        $this->adminTranslator = new Translator($this->appDir,'Admin', $lang_name);
    }

    /**
     *
     */
    public function beforeRender()
    {
        parent::beforeRender();

        $this->template->setTranslator($this->adminTranslator);
    }

}
