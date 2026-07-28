<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Controls\BBMailer;
use App\Controls\BreadCrumbControl;
use App\Controls\GridFilter;
use App\Database\EntityManagerDecorator;
use App\Model\Entity\UserEntity;
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
    public BBMailer $bbMailer;
    
    /**
     *
     * @var Mails2UsersManager $mail2UsersManager
     * @inject
     */
    public Mails2UsersManager $mail2UsersManager;

    /**
     * EmailPresenter constructor.
     *
     * @param MailsManager $manager
     */
    public function __construct(
        MailsManager $manager,
        private readonly EntityManagerDecorator $em,
    )
    {
        parent::__construct($manager);
    }

    /**
     * @param int|null $id
     */
    public function renderEdit($id = null): void
    {
        parent::renderEdit($id);

        $this->getTemplate()->emails = $this->mail2UsersManager->getAllByLeftJoined($id);
    }
    
    /**
     *
     * @return GridFilter
     */
    protected function createComponentGridFilter(): GridFilter
    {
        $this->gf->setTranslator($this->getTranslator());

        $this->gf->addFilter('multiDelete', null, GridFilter::NOTHING);
        $this->gf->addFilter('mail_id', 'mail_id', GridFilter::INT_EQUAL);
        $this->gf->addFilter('mail_subject', 'mail_subject', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('mail_time', 'mail_time', GridFilter::TEXT_LIKE);

        return $this->gf;
    }

    protected function createComponentEditForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();
        $form->setTranslator($this->getTranslator());

        $form->addText('mail_subject', 'mail_subject:')
            ->setDisabled();

        $form->addTextArea('mail_text', 'mail_text:')
            ->setDisabled();

        return $form;
    }

    protected function createComponentSendForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();

        $form->addText('mail_subject', 'mail_subject:')
            ->setRequired(true);

        $form->addTextArea('mail_text', 'mail_text:')
            ->setRequired(true);

        $form->addSubmit('send', 'mail_send');

        $form->onSuccess[] = [$this, 'sendFormSuccess'];

        return $form;
    }

    /**
     * @param Form      $form
     * @param ArrayHash $values
     */
    public function sendFormSuccess(Form $form, ArrayHash $values): void
    {
        $usersMails = $this->em
            ->getRepository(UserEntity::class)
            ->createQueryBuilder('_u')

            ->select('_u.mail')

            ->getQuery()
            ->getResult();

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
    
    protected function createComponentBreadCrumbAll(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['text' => 'menu_emails']
        ];
        
        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }
    
    protected function createComponentBreadCrumbEdit(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'Email:default', 'text' => 'menu_emails'],
            2 => ['link' => 'Email:edit',    'text' => 'menu_email'],
        ];
        
        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }
    
    protected function createComponentBreadCrumbSend(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'Email:default', 'text' => 'menu_emails'],
            2 => ['link' => 'Email:send',    'text' => 'mail_send'],
        ];
        
        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }
}
