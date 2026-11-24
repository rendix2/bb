<?php declare(strict_types=1);

namespace App\UI\Admin\File\Delete;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\FileEntity;
use App\Model\Service\AttachmentService;
use App\UI\Admin\AdminBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use Nette\Application\UI\Form;
use Nette\Localization\Translator;

/**
 * Deleting an attachment of a post (from the database and the disk; the post stays): a confirmation page with a POST form.
 */
class DeletePresenter extends AdminBasePresenter
{
    private FileEntity $file;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly AttachmentService $attachmentService,
        private readonly MenuComponentFactory $menuFactory,
        private readonly Translator $translator,
    ) {
        parent::__construct();
    }

    public function actionDefault(int $id) : void
    {
        $this->file = $this->em->find(FileEntity::class, $id) ?? $this->error('Attachment was not found.');
    }

    public function renderDefault() : void
    {
        $this->template->file = $this->file;
    }

    protected function createComponentMenu() : MenuComponent
    {
        return $this->menuFactory->create('admin');
    }

    protected function createComponentDeleteForm() : BootstrapForm
    {
        $form = new BootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);
        $form->setTranslator($this->translator);
        $form->addProtection('admin-forum.protection');
        $form->addSubmit('delete', 'admin-file-delete.confirm')
            ->setHtmlAttribute('class', 'btn btn-danger');

        $form->onSuccess[] = function (Form $form) : void {
            $name = $this->file->originalName;
            $this->attachmentService->delete($this->file);
            $this->adminFlash($this->translator->translate('admin-file-delete.success', ['name' => $name]));
            $this->redirect(':Admin:File:List:default');
        };

        return $form;
    }
}
