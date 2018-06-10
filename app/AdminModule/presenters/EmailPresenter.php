<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule\Presenters;

use App\Controls\GridFilter;

/**
 * Description of EmailPresenter
 *
 * @author rendi
 */
class EmailPresenter extends Base\AdminPresenter
{
    /**
     *
     * @var \Nette\Mail\IMailer $mailer
     */
    private $mailer;
    
    /**
     *
     * @var \App\Models\UsersManager $usersManager
     */
    private $usersManager;
    
    public function __construct(\App\Models\MailsManager $manager, \App\Models\UsersManager $usersManager, \Nette\Mail\IMailer $mailer) 
    {
        parent::__construct($manager);
        
        $this->mailer       = $mailer;
        $this->usersManager = $usersManager;
    }
    
    public function startup() {
        parent::startup();
        
        if ($this->getAction() == 'default') {
            $this->gf->setTranslator($this->getAdminTranslator());
            
            $this->gf->addFilter('mail_id', 'mail_id', GridFilter::INT_EQUAL);
            $this->gf->addFilter('mail_subject', 'mail_subject', GridFilter::TEXT_LIKE);
            $this->gf->addFilter('mail_time', 'mail_time', GridFilter::TEXT_LIKE);

            $this->addComponent($this->gf, 'gridFilter');
        }
    }
    
    protected function createComponentEditForm() {
       $form = $this->getBootStrapForm();
       $form->setTranslator($this->getAdminTranslator());
       
       $form->addText('mail_subject', 'mail_subject:')->setDisabled();
       $form->addTextArea('mail_text', 'mail_text:')->setDisabled();
              
       return $form;
    }    

    protected function createComponentSendForm() {
       $form = $this->getBootStrapForm();
       $form->setTranslator($this->getAdminTranslator());
       
       $form->addText('mail_subject', 'mail_subject:')->setRequired(true);
       $form->addTextArea('mail_text', 'mail_text:')->setRequired(true);
       $form->addSubmit('send', 'mail_send');
              
       $form->onSuccess[] = [$this, 'sendFormSuccess'];
       
       return $form;
    }
    
    public function sendFormSuccess(\Nette\Application\UI\Form $form, \Nette\Utils\ArrayHash $values)
    {
        $users      = $this->usersManager->getAll();
        $usersMails = [];
        
        foreach ($users as $user) {
           $usersMails[] = $user->user_email;
        }
        
        $bbMailer = new \App\Controls\BBMailer($this->mailer, $this->getManager());
        
        $bbMailer->addRecepients($usersMails);
        $bbMailer->setSubject($values->email_subject);
        $bbMailer->setText($values->email_text);
        
        try {
            $bbMailer->send();
            
            $this->flashMessage('Mails sent!', self::FLASH_MESSAGE_SUCCESS);
            
        } catch (Nette\InvalidArgumentException $e) {
            $this->flashMessage($e->getMessage(), self::FLASH_MESSAGE_DANGER);
        } catch (FallbackMailerException $e) {
            $this->flashMessage($e->getMessage(), self::FLASH_MESSAGE_DANGER);
        } finally {
            $this->redirect('this');
        }                
    }
}
