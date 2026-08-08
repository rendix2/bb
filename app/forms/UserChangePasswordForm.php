<?php

namespace App\Forms;

use App\Database\EntityManagerDecorator;
use App\Model\Repository\UserRepository;
use App\Presenters\Base\BasePresenter;
use App\Settings\Users;
use Doctrine\DBAL\Exception;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Localization\Translator;
use Nette\Security\Passwords;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

class UserChangePasswordForm extends Control
{
    public function __construct(
        private readonly Translator  $translator,
        private readonly User         $user,
        private readonly Users        $users,

        private readonly EntityManagerDecorator $em,

        private readonly UserRepository $userRepository,

        private readonly Passwords $passwords,
    ) {
        parent::__construct();
    }

    public function render(): void
    {
        $this['changePasswordForm']->render();
    }

    protected function createComponentChangePasswordForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();
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

    public function changePasswordOnValidate(Form $form, ArrayHash $values): void
    {
        if (!$values->user_password) {
            $form->addError('Empty password.');
        }

        if (!$values->user_last_password && !$this->user->isInRole('admin')) {
            $form->addError('Empty last password.');
        }

        $user = $this->userRepository->findOneBy(
            [
                'id' => $this->user->getId(),
            ]
        );

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
    public function changePasswordSuccess(Form $form, ArrayHash $values): void
    {
        $userEntity = $this->userRepository->findOneBy(
            [
                'id' => $this->user->getId(),
            ]
        );

        $userEntity->password = $this->passwords->hash($values->passwod);

        try {
            $this->em->persist($userEntity);
            $this->em->flush();

            $this->presenter->flashMessage('Password changed.', BasePresenter::FLASH_MESSAGE_SUCCESS);
            $this->presenter->redirect('this');
        } catch (Exception $exception) {
            $this->presenter->flashMessage('Password was not changed.', BasePresenter::FLASH_MESSAGE_SUCCESS);
            $this->presenter->redirect('this');
        }
    }
}
