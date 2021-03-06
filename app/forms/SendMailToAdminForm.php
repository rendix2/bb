<?php

namespace App\Forms;

use App\Controls\BBMailer;
use App\Controls\BootstrapForm;
use App\Models\UsersManager;
use App\Presenters\Base\BasePresenter;
use App\Services\TranslatorFactory;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Description of SendMailToAdmin
 *
 * @author rendix2
 * @package App\Forms
 */
class SendMailToAdminForm extends Control
{
    /**
     *
     * @var TranslatorFactory $translatorFactory
     */
    private $translatorFactory;

    /**
     *
     * @var UsersManager $usersManager
     */
    private $usersManager;
    
    /**
     *
     * @var BBMailer $bbMailer
     */
    private $bbMailer;

    /**
     * SendMailToAdminForm constructor.
     *
     * @param TranslatorFactory $translatorFactory
     * @param UsersManager      $usersManager
     * @param BBMailer          $bbMailer
     */
    public function __construct(
        TranslatorFactory $translatorFactory,
        UsersManager      $usersManager,
        BBMailer          $bbMailer
    ) {
        parent::__construct();
        
        $this->translatorFactory = $translatorFactory;
        $this->usersManager      = $usersManager;
        $this->bbMailer          = $bbMailer;
    }
    
    /**
     * SendMailToAdminForm destructor.
     */
    public function __destruct()
    {
        $this->translatorFactory = null;
        $this->usersManager      = null;
        $this->bbMailer          = null;
    }

    /**
     * SendMailToAdminForm render.
     */
    public function render()
    {
        $this['sendMailToAdmin']->render();
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentSendMailToAdmin()
    {
        $form = BootstrapForm::create();
        $form->setTranslator($this->translatorFactory->getForumTranslator());
        
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
