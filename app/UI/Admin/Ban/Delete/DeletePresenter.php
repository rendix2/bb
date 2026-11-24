<?php declare(strict_types=1);

namespace App\UI\Admin\Ban\Delete;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\BanEntity;
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
 * Lifting a ban: a confirmation page with a POST form (CSRF).
 */
class DeletePresenter extends AdminBasePresenter
{
    private BanEntity $ban;

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
        $this->ban = $this->em->find(BanEntity::class, $id) ?? $this->error('Ban was not found.');
    }

    public function renderDefault() : void
    {
        $this->template->ban = $this->ban;
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
        $form->addSubmit('delete', 'admin-ban-delete.confirm')
            ->setHtmlAttribute('class', 'btn btn-danger');

        $form->onSuccess[] = function (Form $form) : void {
            $name = (string) $this->ban->id;

            try {
                $this->banAdmin->deleteBan($this->ban);
            } catch (AdminException $exception) {
                $form->addError($exception->getMessage());

                return;
            }

            $this->adminFlash($this->translator->translate('admin-ban-delete.success', ['name' => $name]));
            $this->redirect(':Admin:Ban:List:default');
        };

        return $form;
    }
}
