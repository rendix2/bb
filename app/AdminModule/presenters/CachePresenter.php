<?php

namespace App\AdminModule\Presenters;

/**
 * Description of CachePresenter
 *
 * @author rendi
 */
class CachePresenter extends \App\Presenters\Base\BasePresenter {
    
    private $translator;
    
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

    protected function createComponentEditForm() {
        $form = new \App\Controls\BootstrapForm();
        $form->setTranslator($this->translator);
        $form->addSubmit('Delete_all', 'Delete all cache');
        
        $form->onSuccess[] = [$this, 'success'];


        return $form;
    }
    
    public function success(\Nette\Application\UI\Form $form, \Nette\Utils\ArrayHash $values){
        
    }

    public function beforeRender() {
        parent::beforeRender();

        $this->template->setTranslator($this->translator  = new \App\Translator('Admin', $this->getUser()->getIdentity()->getData()['lang_file_name']));
    }

}
