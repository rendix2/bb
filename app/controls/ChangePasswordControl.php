<?php

namespace App\Controls;

/**
 * Description of ChangePasswordControl
 *
 * @author rendi
 */
class ChangePasswordControl extends \Nette\Application\UI\Control {

    const MIN_LENGTH = 7;

    private $userManager;
    private $translator;
    
    private $user;

    public function __construct(\App\Models\UsersManager $userManager, \Nette\Localization\ITranslator $translator, \Nette\Security\User $user) {
        parent::__construct();

        $this->userManager = $userManager;
        $this->translator  = $translator;
        $this->user       = $user;
    }

    protected function createComponentChangePasswordForm() {
        $form = new \App\Controls\BootstrapForm();
        $form->setTranslator($this->translator);
        $form->addGroup('Password');
        
        if (!$this->user->isInRole('admin')){
            $form->addPassword('user_last_password', 'User last password:')->setRequired(true);
        }
               
        $form->addPassword('user_password', 'User password:')->setRequired(true);
        $form->addPassword('user_password_check', 'User password for check:')->setRequired(true);
        $form->addSubmit('send', 'Change password');
        $form->onSuccess[] = [$this, 'changePasswordSuccess'];
        $form->onValidate[] = [$this, 'changePasswordValidate'];

        return $form;
    }

    public function changePasswordValidate(\Nette\Application\UI\Form $form, \Nette\Utils\ArrayHash $values) {
        if (!$values->user_password) {
            $form->addError('Empty password');
        }
        
        if (!$this->user->isInRole('admin') && !$values->user_last_password ){
            $form->add('Empty last password');
        }
        
        $user = $this->userManager->getById($this->user->getId());
        
        if ( !$user ){
            $form->addError('User not exists!');
        }
        
        if ( !$this->user->isInRole('admin') && !\Nette\Security\Passwords::verify($values->user_last_password, $user->user_password) ){
            $form->addError('Last password is incorrect');
        }

        if (mb_strlen($values->user_password) < self::MIN_LENGTH) {
            $form->addError('Password is not long enough.');
        }

        if ($values->user_password !== $values->user_password_check) {
            $form->addError('Password not same.');
        }
    }

    public function changePasswordSuccess(\Nette\Application\UI\Form $form, \Nette\Utils\ArrayHash $values) {
        $result = $this->userManager->update($this->user->getId(), \Nette\Utils\ArrayHash::from(['user_password' => \Nette\Security\Passwords::hash($values->user_password)]));

        if ($result) {
            $this->presenter->flashMessage('Password changed.', \App\Presenters\Base\BasePresenter::FLASH_MESSAGE_SUCCES);
            $this->presenter->redirect('this');
        }
    }

    public function render() {
        $this->template->setFile(__DIR__ . '/templates/changePassword/changePassword.latte');
        $this->template->render();
    }

}
