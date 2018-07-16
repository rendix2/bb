<?php

namespace App\Forms;

/**
 * Description of SendMailToAdmin
 *
 * @author rendi
 */
class SendMailToAdmin extends \Nette\Application\UI\Control
{

    /**
     * @return BootstrapForm
     */
    protected function createComponentSendMailToAdmin()
    {
        $form = $this->getBootstrapForm();
        
        $form->addText('mail_subject', 'Mail subject:')->setRequired('Subject is required.');
        $form->addTextArea('mail_text', 'Mail text:', null, 10)->setRequired('Text is required.');
        
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
        $admins = $this->getManager()
                ->getAllFluent()
                ->where('[user_role_id] = %i', 5)
                ->fetchAll();
        
        $adminsMails = [];
        
        foreach ($admins as $admin) {
            $adminsMails[] = $admin->user_email;
        }
        
        $this->bbMailer->addRecepients($adminsMails);
        $this->bbMailer->setSubject($values->mail_subject);
        $this->bbMailer->setText($values->mail_text);
        $res = $this->bbMailer->send();
        
        if ($res) {
            $this->flashMessage('Mail sent.', self::FLASH_MESSAGE_SUCCESS);
        } else {
            $this->flashMessage('Mail was not sent.', self::FLASH_MESSAGE_DANGER);
        }
        
        $this->redirect('this');
    }    
    
}
