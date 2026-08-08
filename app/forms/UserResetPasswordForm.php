<?php

namespace App\Forms;

use App\Model\Repository\UserRepository;
use App\Presenters\Base\BasePresenter;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Localization\Translator;
use Nette\Utils\ArrayHash;

/**
 * Description of ResetPasswordForm
 *
 * @author rendix2
 * @package App\Forms
 */
class UserResetPasswordForm extends Control
{
    public function __construct(
        private readonly Translator $translator,
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * UserResetPasswordForm render.
     *
     * renders form
     */
    public function render(): void
    {
        $this['resetPasswordForm']->render();
    }

    protected function createComponentResetPasswordForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();
        $form->setTranslator($this->translator);
        $form->addEmail('user_email', 'User email:');
        $form->addSubmit('send', 'Reset');

        $form->onSuccess[] = [$this, 'resetPasswordFormSuccess'];

        return $form;
    }
    
    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function resetPasswordFormSuccess(Form $form, ArrayHash $values): void
    {
        $found_mail = $this->userRepository->findOneByEmail($values->user_email);

        if ($found_mail) {
            // send mail!

            $this->presenter->flashMessage('Email was sent.', BasePresenter::FLASH_MESSAGE_SUCCESS);
        } else {
            $this->presenter->flashMessage('User mail was not found!', BasePresenter::FLASH_MESSAGE_DANGER);
        }
    }
}
