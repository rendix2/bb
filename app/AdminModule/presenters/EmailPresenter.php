<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Controls\BBMailer;
use App\Controls\BreadCrumbControl;
use App\Controls\GridFilter;
use App\Controls\PaginatorControl;
use App\Database\EntityManagerDecorator;
use App\Model\Entity\UserEntity;
use App\Models\Mails2UsersManager;
use App\Models\MailsManager;
use Dibi\DriverException;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\DI\Attributes\Inject;
use Nette\InvalidArgumentException;
use Nette\Localization\Translator;
use Nette\Mail\FallbackMailerException;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;
use Tracy\ILogger;

/**
 * Description of EmailPresenter
 *
 * @author rendix2
 * @method MailsManager getManager()
 * @package App\AdminModule\Presenters
 */
class EmailPresenter extends Presenter
{

    #[Inject]
    public BBMailer $bbMailer;

    #[Inject]
    public Mails2UsersManager $mail2UsersManager;


    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly Translator $translator,
    )
    {
        parent::__construct();
    }

    public function checkRequirements(\ReflectionClass|\ReflectionMethod $element): void
    {
        $user = $this->getUser();

        $user->getStorage()->setNamespace(self::BACK_END_NAMESPACE);

        parent::checkRequirements($element);

        if ($this->getName() !== 'Login' && !$user->isLoggedIn()) {
            $this->redirect(':Admin:Login:default');
        }

        if (!$user->isInRole('admin')) {
            $this->error('You are not admin.');
        }
    }

    public function actionDelete(int $id): void
    {
        if (!is_numeric($id)) {
            $this->error('Parameter is not numeric.');
        }

        $result = $this->getManager()->delete($id);

        if ($result) {
            $this->flashMessage('Item was deleted.', self::FLASH_MESSAGE_SUCCESS);
        } else {
            $this->flashMessage('Item was not deleted.', self::FLASH_MESSAGE_DANGER);
        }

        $this->redirect(':' . $this->getName() . ':default');
    }

    /**
     * @param int $page
     */
    public function actionDefault(int $page = 1): void
    {
        $items = $this->getManager()->getAllFluent();

        $this->gf->applyWhere($items);
        $this->gf->applyOrderBy($items);

        $paginator = new PaginatorControl($items, 20, 5, $page);
        $this->addComponent($paginator, 'paginator');

        if (!$paginator->getCount()) {
            $this->flashMessage(sprintf('No %s.', $this->getTitle()), self::FLASH_MESSAGE_DANGER);
        }

        $this->template->items      = $items->fetchAll();
        $this->template->countItems = $paginator->getCount();
    }

    /**
     * @param int $page
     */
    public function renderDefault(int $page = 1): void
    {
        $this->template->title = $this->getTitleOnDefault();
    }

    /**
     * @param int|null $id
     */
    public function renderEdit($id = null): void
    {
        if ($id) {
            if (!is_numeric($id)) {
                $this->error('Parameter $id of CrudPresenter::renderEdit($id) is not numeric.');
            }

            $item = $this->getManager()->getById($id);

            if (!$item) {
                $this->error('Item $' . $this->getTitle() . '[' . $id . '] was not found.');
            }

            $this['editForm']->setDefaults($item);

            $this->template->item_id = $id;
            $this->template->item    = $item;
            $this->template->title   = $this->getTitleOnEdit();
        } else {
            $this->template->item_id = null;
            $this->template->title   = $this->getTitleOnAdd();
            $this->template->item    = [];

            $this['editForm']->setDefaults([]);
        }

        $this->getTemplate()->emails = $this->mail2UsersManager->getAllByLeftJoined($id);
    }
    
    /**
     *
     * @return GridFilter
     */
    protected function createComponentGridFilter(): GridFilter
    {
        $this->gf->setTranslator($this->translator);

        $this->gf->addFilter('multiDelete', null, GridFilter::NOTHING);
        $this->gf->addFilter('mail_id', 'mail_id', GridFilter::INT_EQUAL);
        $this->gf->addFilter('mail_subject', 'mail_subject', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('mail_time', 'mail_time', GridFilter::TEXT_LIKE);

        return $this->gf;
    }

    protected function createComponentEditForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();
        $form->setTranslator($this->translator);

        $form->addText('mail_subject', 'mail_subject:')
            ->setDisabled();

        $form->addTextArea('mail_text', 'mail_text:')
            ->setDisabled();

        return $form;
    }

    public function editFormSuccess(Form $form, ArrayHash $values): void
    {
        $id = $this->getParameter('id');

        try {
            if ($id) {
                $result = $this->getManager()->update($id, $values);
            } else {
                $result = $id = $this->getManager()->add($values);
            }

            if ($result) {
                $this->flashMessage($this->getTitle() . ' was saved.', self::FLASH_MESSAGE_SUCCESS);
            } else {
                $this->flashMessage('Nothing to save.', self::FLASH_MESSAGE_INFO);
            }
        } catch (DriverException $e) {
            $this->flashMessage(
                'There was some problem during saving into database. Form was NOT saved.',
                self::FLASH_MESSAGE_DANGER
            );

            Debugger::log($e->getMessage(), ILogger::CRITICAL);
        }

        $this->redirect(':' . $this->getName() . ':default');
    }

    protected function createComponentSendForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();

        $form->addText('subject', 'Subject')
            ->setRequired(true);

        $form->addTextArea('text', 'Text')
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
        
        return new BreadCrumbControl($breadCrumb, $this->translator);
    }
    
    protected function createComponentBreadCrumbEdit(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'Email:default', 'text' => 'menu_emails'],
            2 => ['link' => 'Email:edit',    'text' => 'menu_email'],
        ];
        
        return new BreadCrumbControl($breadCrumb, $this->translator);
    }
    
    protected function createComponentBreadCrumbSend(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'Email:default', 'text' => 'menu_emails'],
            2 => ['link' => 'Email:send',    'text' => 'mail_send'],
        ];
        
        return new BreadCrumbControl($breadCrumb, $this->translator);
    }
}
