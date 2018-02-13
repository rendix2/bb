<?php

namespace App\Controls;

use App\Models\UsersManager;
use App\Presenters\Base\BasePresenter;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nette\Security\Passwords;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

/**
 * Description of ChangePasswordControl
 *
 * @author rendi
 */
class ChangePasswordControl extends Control
{

    const MIN_LENGTH = 7;

    /**
     * @var UsersManager $userManager
     */
    private $userManager;

    /**
     * @var ITranslator $translator
     */
    private $translator;

    /**
     * @var User $user
     */
    private $user;

    /**
     * ChangePasswordControl constructor.
     *
     * @param UsersManager $userManager
     * @param ITranslator  $translator
     * @param User         $user
     */
    public function __construct(UsersManager $userManager, ITranslator $translator, User $user)
    {
        parent::__construct();

        $this->userManager = $userManager;
        $this->translator  = $translator;
        $this->user        = $user;
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function changePasswordSuccess(Form $form, ArrayHash $values)
    {
        $result = $this->userManager->update($this->user->getId(), ArrayHash::from(['user_password' => Passwords::hash($values->user_password)]));

        if ($result) {
            $this->presenter->flashMessage('Password changed.', BasePresenter::FLASH_MESSAGE_SUCCES);
            $this->presenter->redirect('this');
        }
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function changePasswordValidate(Form $form, ArrayHash $values)
    {
        if (!$values->user_password) {
            $form->addError('Empty password');
        }

        if (!$this->user->isInRole('admin') && !$values->user_last_password) {
            $form->add('Empty last password');
        }

        $user = $this->userManager->getById($this->user->getId());

        if (!$user) {
            $form->addError('User not exists!');
        }

        if (!$this->user->isInRole('admin') && !Passwords::verify($values->user_last_password, $user->user_password)) {
            $form->addError('Last password is incorrect');
        }

        if (mb_strlen($values->user_password) < self::MIN_LENGTH) {
            $form->addError('Password is not long enough.');
        }

        if ($values->user_password !== $values->user_password_check) {
            $form->addError('Password not same.');
        }
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/templates/changePassword/changePassword.latte');
        $this->template->render();
    }

    protected function createComponentChangePasswordForm()
    {
        $form = new BootstrapForm();
        $form->setTranslator($this->translator);
        $form->addGroup('Password');

        if (!$this->user->isInRole('admin')) {
            $form->addPassword('user_last_password', 'User last password:')->setRequired(true);
        }

        $form->addPassword('user_password', 'User password:')->setRequired(true);
        $form->addPassword('user_password_check', 'User password for check:')->setRequired(true);
        $form->addSubmit('send', 'Change password');
        $form->onSuccess[] = [
            $this,
            'changePasswordSuccess'
        ];
        $form->onValidate[] = [
            $this,
            'changePasswordValidate'
        ];

        return $form;
    }

}
