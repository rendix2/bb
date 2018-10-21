<?php

namespace App\Forms;

use App\Controls\BootstrapForm;
use App\Models\UsersManager;
use App\Presenters\Base\BasePresenter;
use App\Settings\Users;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nette\Security\Passwords;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

/**
 * Description of ChangePasswordControl
 *
 * @author rendix2
 */
class UserChangePasswordForm extends Control
{

    /**
     * @var UsersManager $userManager
     */
    private $userManager;

    /**
     * @var ITranslator $translator
     */
    private $translator;

    /**
     * nette user
     *
     * @var User $user
     */
    private $user;
    
    /**
     * user config from neon
     *
     * @var Users $users
     */
    private $users;

    /**
     * ChangePasswordControl constructor.
     *
     * @param UsersManager $userManager
     * @param ITranslator  $translator
     * @param User         $user
     * @param Users        $users
     */
    public function __construct(UsersManager $userManager, ITranslator $translator, User $user, Users $users)
    {
        parent::__construct();

        $this->userManager = $userManager;
        $this->translator  = $translator;
        $this->user        = $user;
        $this->users       = $users;
    }
    
    public function __destruct()
    {
        $this->userManager = null;
        $this->translator  = null;
        $this->user        = null;
        $this->users       = null;
    }

        /**
     * renders control
     */
    public function render()
    {
        $this['changePasswordForm']->render();
    }
    
    /**
     * @return BootstrapForm
     */
    protected function createComponentChangePasswordForm()
    {
        $form = BootstrapForm::create();
        $form->setTranslator($this->translator);
        $form->addGroup('Password');

        if (!$this->user->isInRole('admin')) {
            $form->addPassword('user_last_password', 'User last password:')->setRequired(true);
        }

        $form->addPassword('user_password', 'User password:')->setRequired(true);
        $form->addPassword('user_password_check', 'User password for check:')->setRequired(true);
        $form->addSubmit('send', 'Change password');
        $form->onSuccess[] = [$this, 'changePasswordSuccess'];
        $form->onValidate[] = [$this, 'changePasswordOnValidate'];

        return $form;
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function changePasswordOnValidate(Form $form, ArrayHash $values)
    {
        if (!$values->user_password) {
            $form->addError('Empty password.');
        }

        if (!$values->user_last_password && !$this->user->isInRole('admin')) {
            $form->addError('Empty last password.');
        }

        $user = $this->userManager->getById($this->user->getId());

        if (!$user) {
            $form->addError('User not exists!');
        }

        if (!$this->user->isInRole('admin') && !Passwords::verify($values->user_last_password, $user->user_password)) {
            $form->addError('Last password is incorrect.');
        }

        if (mb_strlen($values->user_password) <= $this->users->get()['minUserPasswordLength']) {
            $form->addError('Password is not long enough.');
        }

        if ($values->user_password !== $values->user_password_check) {
            $form->addError('Password not same.');
        }
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function changePasswordSuccess(Form $form, ArrayHash $values)
    {
        $result = $this->userManager->update(
            $this->user->getId(),
            ArrayHash::from(
                ['user_password' => Passwords::hash($values->user_password)]
            )
        );

        if ($result) {
            $this->presenter->flashMessage('Password changed.', BasePresenter::FLASH_MESSAGE_SUCCESS);
            $this->presenter->redirect('this');
        }
    }
}
