<?php

namespace App\Forms;

use App\Controls\BootstrapForm;
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
    private $translateFactory;
    
    /**
     *
     * @var UsersManager $usersManager
     */
    private $usersManager;

    /**
     * UserResetPasswordForm constructor.
     *
     * @param TranslatorFactory $translatorFactory
     * @param UsersManager      $usersManager
     */
    public function __construct(
        TranslatorFactory $translatorFactory,
        UsersManager      $usersManager
    ) {
        parent::__construct();
        
        $this->translateFactory = $translatorFactory;
        $this->usersManager     = $usersManager;
    }
    
    /**
     * UserResetPasswordForm destructor
     */
    public function __destruct()
    {
        $this->translateFactory = null;
        $this->usersManager     = null;
    }

    /**
     * UserResetPasswordForm render.
     *
     * renders form
     */
    public function render()
    {
        $this['resetPasswordForm']->render();
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentResetPasswordForm()
    {
        $form = BootstrapForm::create();
        $form->setTranslator($this->translateFactory->getForumTranslator());
        $form->addEmail(
            'user_email',
            'User email:'
        );
        $form->addSubmit(
            'send',
            'Reset'
        );
        $form->onSuccess[] = [
            $this,
            'resetPasswordFormSuccess'
        ];

        return $form;
    }
    
    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function resetPasswordFormSuccess(Form $form, ArrayHash $values)
    {
        $found_mail = $this->usersManager->getByEmail($values->user_email);

        if ($found_mail) {
            // send mail!

            $this->presenter->flashMessage('Email was sent.', BasePresenter::FLASH_MESSAGE_SUCCESS);
        } else {
            $this->presenter->flashMessage('User mail was not found!', BasePresenter::FLASH_MESSAGE_DANGER);
        }
    }
}
