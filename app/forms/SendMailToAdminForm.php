<?php

namespace App\Forms;

use App\Controls\BBMailer;
use App\Models\UsersManager;
use App\Presenters\Base\BasePresenter;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Localization\Translator;
use Nette\Utils\ArrayHash;

/**
 * Description of SendMailToAdmin
 *
 * @author rendix2
 * @package App\Forms
 */
class SendMailToAdminForm extends Control
{
    private UsersManager $usersManager;
    
    private BBMailer $bbMailer;

    public function __construct(
        private readonly Translator $translator,
        UsersManager      $usersManager,
        BBMailer          $bbMailer
    ) {
        parent::__construct();

        $this->usersManager      = $usersManager;
        $this->bbMailer          = $bbMailer;
    }

    /**
     * SendMailToAdminForm render.
     */
    public function render(): void
    {
        $this['sendMailToAdmin']->render();
    }

    protected function createComponentSendMailToAdmin(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();
        $form->setTranslator($this->translator);
        
        $form->addText('mail_subject', 'Mail subject:')
            ->setRequired('Subject is required.');
        $form->addTextArea('mail_text', 'Mail text:', null, 10)
            ->setRequired('Text is required.');
        
        $form->addSubmit('send', 'Send mail');
        $form->onSuccess[] = [$this, 'sendMailToAdminSuccess'];
        
        return $form;
    }

    /**
     *
     * @param Form $form
     * @param ArrayHash $values
     */
    public function sendMailToAdminSuccess(Form $form, ArrayHash $values)
    {
        $admins = $this->usersManager
                ->getAllFluent()
                ->where('[user_role_id] = %i', 5)
                ->fetchAll();
        
        $adminsMails = \App\Utils::arrayObjectColumn($admins, 'user_email');
        
        $this->bbMailer->addRecipients($adminsMails);
        $this->bbMailer->setSubject($values->mail_subject);
        $this->bbMailer->setText($values->mail_text);
        $res = $this->bbMailer->send();
        
        if ($res) {
            $this->presenter->flashMessage('Mail was sent.', BasePresenter::FLASH_MESSAGE_SUCCESS);
        } else {
            $this->presenter->flashMessage('Mail was not sent.', BasePresenter::FLASH_MESSAGE_DANGER);
        }
        
        $this->presenter->redirect('this');
    }
}
