<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Controls\BBMailer;
use App\Controls\BootstrapForm;
use App\Controls\BreadCrumbControl;
use App\Controls\GridFilter;
use App\Models\Mails2UsersManager;
use App\Models\MailsManager;
use App\Models\UsersManager;
use App\Utils;
use Nette\Application\UI\Form;
use Nette\InvalidArgumentException;
use Nette\Mail\FallbackMailerException;
use Nette\Utils\ArrayHash;

/**
 * Description of EmailPresenter
 *
 * @author rendix2
 * @method MailsManager getManager()
 * @package App\AdminModule\Presenters
 */
class EmailPresenter extends AdminPresenter
{
    
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
     * @var UsersManager $usersManager
     * @inject
     */
    public $usersManager;

    /**
     * EmailPresenter constructor.
     *
     * @param MailsManager $manager
     */
    public function __construct(MailsManager $manager)
    {
        parent::__construct($manager);
    }
    
    /**
     * EmailPresenter destructor.
     */
    public function __destruct()
    {
        $this->bbMailer          = null;
        $this->mail2UsersManager = null;
        $this->usersManager      = null;
        
        parent::__destruct();
    }

    /**
     * @param int|null $id
     */
    public function renderEdit($id = null)
    {
        parent::renderEdit($id);

        $this->template->emails = $this->mail2UsersManager->getAllByLeftJoined($id);
    }
    
    /**
     *
     * @return GridFilter
     */
    protected function createComponentGridFilter()
    {
        $this->gf->setTranslator($this->getTranslator());

        $this->gf->addFilter('multiDelete', null, GridFilter::NOTHING);
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
        $form->setTranslator($this->getTranslator());

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
        $usersMails = Utils::arrayObjectColumn($users, 'user_email');

        $this->bbMailer->addRecipients($usersMails);
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
    
    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbAll()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['text' => 'menu_emails']
        ];
        
        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }
    
    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbEdit()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'Email:default', 'text' => 'menu_emails'],
            2 => ['link' => 'Email:edit',    'text' => 'menu_email'],
        ];
        
        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }
    
    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbSend()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'Email:default', 'text' => 'menu_emails'],
            2 => ['link' => 'Email:send',    'text' => 'mail_send'],
        ];
        
        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }
}
