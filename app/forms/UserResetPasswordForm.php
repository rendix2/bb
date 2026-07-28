<?php

namespace App\Forms;

use App\Model\Repository\UserRepository;
use App\Models\UsersManager;
use App\Presenters\Base\BasePresenter;
use App\Services\TranslatorFactory;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Description of ResetPasswordForm
 *
 * @author rendix2
 * @package App\Forms
 */
class UserResetPasswordForm extends Control
{
    /**
     *
     * @var TranslatorFactory $translatorFactory
     */
    private TranslatorFactory $translateFactory;


    public function __construct(
        TranslatorFactory $translatorFactory,
        private readonly UserRepository $userRepository,
    ) {
        parent::__construct();
        
        $this->translateFactory = $translatorFactory;
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
        $form->setTranslator($this->translateFactory->getForumTranslator());
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
