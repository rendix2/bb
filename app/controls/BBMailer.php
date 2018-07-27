<?php

namespace App\Controls;

use App\Models\Mails2UsersManager;
use App\Models\MailsManager;
use App\Models\UsersManager;
use App\Settings\Email;
use Latte\Engine;
use Nette\Mail\IMailer;
use Nette\Mail\Message;

/**
 * Description of BBMailer
 *
 * @author rendix2
 */
class BBMailer
{
    /**
     *
     * @var Message $message
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
     * @var Mails2UsersManager $mails2users;
     */
    private $mails2users;
    
    /**
     *
     * @var UsersManager $usersManager
     */
    private $usersManager;

    /**
     * BBMailer constructor.
     *
     * @param IMailer            $mailer
     * @param MailsManager       $manager
     * @param Email              $email
     * @param Mails2UsersManager $mails2users
     * @param UsersManager       $usersManager
     */
    public function __construct(
        IMailer $mailer,
        MailsManager $manager,
        Email $email,
        Mails2UsersManager $mails2users,
        UsersManager $usersManager
    ) {
        $this->mailer  = $mailer;
        $this->message = new Message();
        $this->message->setFrom($email->getMail());
        
        $this->manager      = $manager;
        $this->mails2users  = $mails2users;
        $this->usersManager = $usersManager;
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
     * @return BBMailer
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
     * @return BBMailer
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
            'mail_from'    => $this->message->getFrom(),
            'mail_time'    => time()
        
        ];
        
        $emails   = $this->usersManager->getByEmails($this->recepients);        
        $email_id = $this->manager->add(\Nette\Utils\ArrayHash::from($item_data));
        
        $emailsArray = [];       
        
        foreach ($emails as $email) {
            $emailsArray[] = $email->user_id;
        }
        
        $this->mails2users->addByLeft($email_id, $emailsArray);
    }
}
