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

    public function __construct(\App\Models\UsersManager $userManager, \Nette\Localization\ITranslator $translator) {
        parent::__construct();

        $this->userManager = $userManager;
        $this->translator = $translator;
    }

    protected function createComponentChangePasswordForm() {
        $form = new \App\Controls\BootstrapForm();
        $form->setTranslator($this->translator);

        $form->addHidden('user_id');
        $form->addPassword('user_password', 'User password:')->setRequired(true);
        $form->addPassword('user_password_check', 'User password for check:')->setRequired(true);
        $form->addSubmit('send', 'Send');
        $form->onSuccess[] = [$this, 'changePasswordSuccess'];
        $form->onValidate[] = [$this, 'changePasswordValidate'];

        return $form;
    }

    public function changePasswordValidate(\Nette\Application\UI\Form $form, \Nette\Utils\ArrayHash $values) {
        if (!$values->user_id) {
            $form->addError('User id is not selected!');
        }

        if (!$values->user_password) {
            $form->addError('Empty password');
        }

        if (mb_strlen($values->user_password) < self::MIN_LENGTH) {
            $form->addError('Password is not long enough.');
        }

        if ($values->user_password !== $values->user_password_check) {
            $form->addError('Password not same.');
        }
    }

    public function changePasswordSuccess(\Nette\Application\UI\Form $form, \Nette\Utils\ArrayHash $values) {
        $result = $this->userManager->update($values->user_id, \Nette\Utils\ArrayHash::from(['user_password' => \Nette\Security\Passwords::hash($values->user_password)]));

        if ($result) {
            $this->presenter->flashMessage('Password changed.', \App\Presenters\Base\BasePresenter::FLASH_MESSAGE_SUCCES);
            $this->presenter->redirect('this');
        }
    }

    public function render($user_id) {
        $this->template->setFile(__DIR__ . '/templates/changePassword/changePassword.latte');

        $this['changePasswordForm']->setDefaults(['user_id' => $user_id]);
        $this->template->render();
    }

}
