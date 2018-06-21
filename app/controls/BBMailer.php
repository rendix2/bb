<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controls;

use App\Models\MailsManager;
use Latte\Engine;
use Nette\Mail\IMailer;

/**
 * Description of BBMailer
 *
 * @author rendi
 */
class BBMailer
{
    
    /**
     *
     * @var \Nette\Mail\Message $message
     */
    private $message;
    
    /**
     *
     * @var IMailer $smtp
     */
    private $mailer;
    
    /**
     *
     * @var array $recepients;
     */
    private $recepients;
    
    /**
     *
     * @var MailsManager $manager
     */
    private $manager;

    /**
     * BBMailer constructor.
     *
     * @param IMailer      $mailer
     * @param MailsManager $manager
     */
    /**
     * BBMailer constructor.
     *
     * @param IMailer      $mailer
     * @param MailsManager $manager
     */
    public function __construct(IMailer $mailer, MailsManager $manager)
    {
        $this->mailer  = $mailer;
        $this->message = new \Nette\Mail\Message();
        $this->message->setFrom('a@a.a');
        
        $this->manager = $manager;
    }

    /**
     * @param array $recepients
     *
     * @return $this
     */
    /**
     * @param array $recepients
     *
     * @return $this
     */
    public function addRecepients(array $recepients)
    {
        foreach ($recepients as $recepient) {
            $this->message->addTo($recepient);
        }
        
        $this->recepients = $recepients;
        
        return $this;
    }

    /**
     * @param $subject
     *
     * @return $this
     */
    /**
     * @param $subject
     *
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->message->setSubject($subject);
        
        return $this;
    }

    /**
     * @param string $input
     * @param null|mixed   $variables
     *
     * @return $this
     */
    public function setText($input, $variables = null)
    {
        if (file_exists($input)) {
            $latte = new Engine();

            $latte->setTempDirectory(__DIR__.'/..temp/');
            
            $this->message->setHtmlBody($latte->renderToString($input, $variables));
        } else {
            $this->message->setBody($input);
        }
        
        return $this;
    }

    /**
     *
     */
    public function send()
    {
//        $smtp       = new \Nette\Mail\SmtpMailer($config);
//        $sendMailer = new \Nette\Mail\SendmailMailer();
       
        
        $this->saveMailHistory();
        
        $mailer = new \Nette\Mail\FallbackMailer([
//            $smtp,
            $this->mailer
        ]);
        
        try {
            $mailer->send($this->message);
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     *
     */
    private function saveMailHistory()
    {
        $item_data = [
            'mail_text'    => $this->message->getBody(),
            'mail_subject' => $this->message->getSubject(),
            'mail_to'      => implode(', ', $this->recepients),
            'mail_from'    => $this->message->getFrom(),
            'mail_time'    => time()
        
        ];
        
        $this->manager->add(\Nette\Utils\ArrayHash::from($item_data));
    }
}
