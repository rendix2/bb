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
     * @var \App\Services\TranslatorFactory $translatorFactory
     * @inject
     */
    public $translatorFactory;

    /**
     * AdminPresenter constructor.
     *
     * @param CrudManager $manager
     */
    public function __construct(CrudManager $manager)
    {
        parent::__construct($manager);
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
     */
    public function startup()
    {
        parent::startup();

        $user = $this->getUser();

        if (!$user->isLoggedIn()) {
            $this->redirect(':Admin:Login:default');
        }

        if (!$user->isInRole('admin')) {
            $this->error('You are not admin.');
        }

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
}
