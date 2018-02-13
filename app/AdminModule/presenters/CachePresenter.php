<?php

namespace App\AdminModule\Presenters;

use App\Controls\BootstrapForm;
use App\Presenters\Base\BasePresenter;
use App\Translator;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nette\Utils\ArrayHash;

/**
 * Description of CachePresenter
 *
 * @author rendi
 */
class CachePresenter extends BasePresenter {

    /**
     * @var ITranslator $translator
     */
    private $translator;

    /**
     *
     */
    public function startup() {
        parent::startup();

        $user = $this->getUser();

        if (!$this->user->isLoggedIn()) {
            if ($user->logoutReason === IUserStorage::INACTIVITY) {
                $this->flashMessage('You have been signed out due to inactivity. Please sign in again.');
            }
            $this->redirect('Login:default', ['backlink' => $this->storeRequest()]);
        }
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentEditForm() {
        $form = new BootstrapForm();
        $form->setTranslator($this->translator);
        $form->addSubmit('Delete_all', 'Delete all cache');
        
        $form->onSuccess[] = [$this, 'success'];


        return $form;
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function success(Form $form, ArrayHash $values){
        
    }

    /**
     *
     */
    public function beforeRender() {
        parent::beforeRender();

        $this->template->setTranslator($this->translator  = new Translator('Admin', $this->getUser()->getIdentity()->getData()['lang_file_name']));
    }

}
