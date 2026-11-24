<?php declare(strict_types=1);

namespace App\UI\Admin\BanReason\Delete;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\BanReasonEntity;
use App\Model\Service\AdminException;
use App\Model\Service\BanAdminService;
use App\UI\Admin\AdminBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use Nette\Application\UI\Form;
use Nette\Localization\Translator;

/**
 * Deleting a predefined reason of a ban (the bans keep their copy of the text): a confirmation page with a POST form (CSRF).
 */
class DeletePresenter extends AdminBasePresenter
{
    private BanReasonEntity $reason;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly BanAdminService $banAdmin,
        private readonly MenuComponentFactory $menuFactory,
        private readonly Translator $translator,
    ) {
        parent::__construct();
    }

    public function actionDefault(int $id) : void
    {
        $this->reason = $this->em->find(BanReasonEntity::class, $id) ?? $this->error('Reason was not found.');
    }

    public function renderDefault() : void
    {
        $this->template->reason = $this->reason;
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
        $form->addSubmit('delete', 'admin-ban-reason-delete.confirm')
            ->setHtmlAttribute('class', 'btn btn-danger');

        $form->onSuccess[] = function (Form $form) : void {
            $name = $this->reason->text;

            try {
                $this->banAdmin->deleteReason($this->reason);
            } catch (AdminException $exception) {
                $form->addError($exception->getMessage());

                return;
            }

            $this->adminFlash($this->translator->translate('admin-ban-reason-delete.success', ['name' => $name]));
            $this->redirect(':Admin:BanReason:List:default');
        };

        return $form;
    }
}
