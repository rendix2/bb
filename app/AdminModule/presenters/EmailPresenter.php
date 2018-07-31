<?php

namespace App\AdminModule\Presenters;

use App\Controls\BBMailer;
use App\Controls\BootstrapForm;
use App\Controls\GridFilter;
use App\Models\MailsManager;
use App\Models\UsersManager;
use App\Models\Mails2UsersManager;
use Nette\Application\UI\Form;
use Nette\InvalidArgumentException;
use Nette\Mail\FallbackMailerException;
use Nette\Utils\ArrayHash;

/**
 * Description of EmailPresenter
 *
 * @author rendi
 */
class EmailPresenter extends Base\AdminPresenter
{
    /**
     *
     * @var UsersManager $usersManager
     */
    private $usersManager;
    
    /**
     * @var BBMailer $bbMailer
     * @inject
     */
    public $bbMailer;
    
    /**
     *
     * @var Mails2UsersManager $mail2UsersManager
     * @inject
     */
    public $mail2UsersManager;

    /**
     * EmailPresenter constructor.
     *
     * @param MailsManager $manager
     * @param UsersManager $usersManager
     */
    public function __construct(MailsManager $manager, UsersManager $usersManager)
    {
        parent::__construct($manager);

        $this->usersManager = $usersManager;
    }
    
    /**
     * 
     * @param int|null $id
     */
    public function renderEdit($id = null)
    {
        parent::renderEdit($id);
        
        $this->template->emails = $this->mail2UsersManager->getAllJoinedByLeft($id);                
    }
    
    /**
     * 
     * @return GridFilter
     */
    protected function createComponentGridFilter()
    {
        $this->gf->setTranslator($this->getAdminTranslator());

        $this->gf->addFilter('mail_id', 'mail_id', GridFilter::INT_EQUAL);
        $this->gf->addFilter('mail_subject', 'mail_subject', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('mail_time', 'mail_time', GridFilter::TEXT_LIKE);

        return $this->gf;
    }

    /**
     * @return BootstrapForm|mixed
     */
    protected function createComponentEditForm()
    {
        $form = $this->getBootstrapForm();
        $form->setTranslator($this->getAdminTranslator());

        $form->addText('mail_subject', 'mail_subject:')->setDisabled();
        $form->addTextArea('mail_text', 'mail_text:')->setDisabled();

        return $form;
    }

    /**
     * @return BootstrapForm
     */
    protected function createComponentSendForm()
    {
        $form = $this->getBootstrapForm();

        $form->addText('mail_subject', 'mail_subject:')->setRequired(true);
        $form->addTextArea('mail_text', 'mail_text:')->setRequired(true);
        $form->addSubmit('send', 'mail_send');

        $form->onSuccess[] = [$this, 'sendFormSuccess'];

        return $form;
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function sendFormSuccess(Form $form, ArrayHash $values)
    {
        $users      = $this->usersManager->getAll();
        $usersMails = [];

        foreach ($users as $user) {
            $usersMails[] = $user->user_email;
        }

        $this->bbMailer->addRecepients($usersMails);
        $this->bbMailer->setSubject($values->email_subject);
        $this->bbMailer->setText($values->email_text);

        try {
            $this->bbMailer->send();

            $this->flashMessage('Mails sent!', self::FLASH_MESSAGE_SUCCESS);
        } catch (InvalidArgumentException $e) {
            $this->flashMessage($e->getMessage(), self::FLASH_MESSAGE_DANGER);
        } catch (FallbackMailerException $e) {
            $this->flashMessage($e->getMessage(), self::FLASH_MESSAGE_DANGER);
        } finally {
            $this->redirect('this');
        }
    }
}
