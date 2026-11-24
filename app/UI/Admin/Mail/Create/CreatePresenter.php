<?php declare(strict_types=1);

namespace App\UI\Admin\Mail\Create;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\UserEntity;
use App\Model\Service\AdminException;
use App\Model\Service\MassMailService;
use App\UI\Admin\AdminBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use Nette\Application\UI\Form;
use Nette\Localization\Translator;

/**
 * A new mass e-mail to all active users (slice 7e4): the subject and the text in Markdown, queued for each recipient.
 */
class CreatePresenter extends AdminBasePresenter
{
    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly MassMailService $massMailService,
        private readonly MenuComponentFactory $menuFactory,
        private readonly Translator $translator,
    ) {
        parent::__construct();
    }

    public function renderDefault() : void
    {
        $this->template->recipientCount = count($this->massMailService->recipients());
    }

    protected function createComponentMenu() : MenuComponent
    {
        return $this->menuFactory->create('admin');
    }

    protected function createComponentMailForm() : BootstrapForm
    {
        $form = new BootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);
        $form->setTranslator($this->translator);
        $form->addProtection('admin-forum.protection');

        $form->addText('subject', 'admin-mail-create.subject')
            ->setRequired('admin-mail.error.subject')
            ->addRule($form::MaxLength, 'admin-mail.error.subject', 1024);
        $form->addTextArea('body', 'admin-mail-create.body', rows: 12)
            ->setOption('description', $this->translator->translate('admin-mail-create.bodyHelp'))
            ->setRequired('admin-mail.error.body');
        $form->addSubmit('send', 'admin-mail-create.send')
            ->setHtmlAttribute('class', 'btn btn-danger');

        $form->onSuccess[] = function (Form $form) : void {
            /** @var array{subject: string, body: string} $values */
            $values = $form->getValues('array');
            $admin = $this->em->find(UserEntity::class, $this->getUser()->getId()) ?? $this->error('Admin was not found.');

            try {
                $massMail = $this->massMailService->send($admin, $values['subject'], $values['body']);
            } catch (AdminException $exception) {
                $form->addError($exception->getMessage());

                return;
            }

            $this->adminFlash($this->translator->translate('admin-mail-create.success', ['count' => $massMail->recipientCount]));
            $this->redirect(':Admin:Mail:List:default');
        };

        return $form;
    }
}
