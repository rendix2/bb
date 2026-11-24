<?php declare(strict_types=1);

namespace App\UI\Admin\BanReason\Edit;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\BanReasonEntity;
use App\UI\Admin\Accessory\BanReasonFormFactory;
use App\UI\Admin\AdminBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use Nette\Localization\Translator;

/**
 * Editing a predefined reason of a ban.
 */
class EditPresenter extends AdminBasePresenter
{
    private BanReasonEntity $reason;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly BanReasonFormFactory $banReasonFormFactory,
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

    protected function createComponentReasonForm() : BootstrapForm
    {
        return $this->banReasonFormFactory->create($this->reason, function (BanReasonEntity $reason) : void {
            $this->adminFlash($this->translator->translate('admin-ban-reason-edit.success', ['name' => $reason->text]));
            $this->redirect(':Admin:BanReason:List:default');
        });
    }
}
