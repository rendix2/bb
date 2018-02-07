<?php

namespace App\ForumModule\Presenters;

/**
 * Description of LoginPresenter
 *
 * @author rendi
 */
class LoginPresenter extends \App\Presenters\Base\BasePresenter {

    private $authenticator;
    
    /** 
     * @persistent
     */
    public $backlink = '';

    public function __construct(\App\Authenticator $authenticator) {
        parent::__construct();

        $this->authenticator = $authenticator;
    }

    public function startup() {
        parent::startup();

        $this->getUser()->setAuthenticator($this->authenticator);
    }

    protected function createComponentLoginForm() {
        $form = new \App\Controls\BootstrapForm();

        $form->addText('user_name', 'Login:');
        $form->addPassword('user_password', 'Password:');
        $form->addSubmit('send');
        $form->onSuccess[] = [$this, 'loginForumSuccess'];

        return $form;
    }

    public function loginForumSuccess(\Nette\Application\UI\Form $form, \Nette\Utils\ArrayHash $values) {
        $user = $this->getUser();
        
        $user->login($values->user_name, $values->user_password);
        $user->setExpiration('1 hour');

        $this->flashMessage('Successfuly logged in.', self::FLASH_MESSAGE_SUCCES);
        $this->restoreRequest($this->backlink);
        $this->redirect('Index:default');
   }
}
