<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controls;

/**
 * Description of BBMailer
 *
 * @author rendi
 */
class BBMailer
{
    
    /**
     *
     * @var Nette\Mail\Message $message
     */
    private $message;
    
    /**
     *
     * @var \Nette\Mail\IMailer $smtp
     */
    private $mailer;

    
    public function __construct(\Nette\Mail\IMailer $mailer)
    {       
        $this->mailer  = $mailer;
        $this->message = new \Nette\Mail\Message();
        $this->message->setFrom('a@a.a');
    }
    
    public function addRecepients(array $recepients)
    {
        foreach ($recepients as $recepient) {
            $this->message->addTo($recepient);
        }
        
        return $this;
    }
    
    public function setSubject($subject)
    {
        $this->message->setSubject($subject);
        
        return $this;
    }

    public function setText($input, $variables = null)
    {
        if (file_exists($input)) {
            $latte = new Latte\Engine;

            $latte->setTempDirectory(__DIR__.'/..temp/');
            
            $this->message->setHtmlBody($latte->renderToString($input, $variables));
        } else {
            $this->message->setBody($input);
        }
        
        return $this;
    }
    
    public function send()
    {
//        $smtp       = new \Nette\Mail\SmtpMailer($config);
//        $sendMailer = new \Nette\Mail\SendmailMailer();
       
        
        $mailer = new \Nette\Mail\FallbackMailer([
//            $smtp,
            $this->mailer
        ]);
        $mailer->send($this->message);
        
        
        //$this->mailer->send($this->message);
    }
}
