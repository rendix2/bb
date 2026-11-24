<?php declare(strict_types=1);

namespace App\UI\Admin\Ban\Edit;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\BanEntity;
use App\UI\Admin\Accessory\BanFormFactory;
use App\UI\Admin\AdminBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use Nette\Localization\Translator;

/**
 * Editing a ban.
 */
class EditPresenter extends AdminBasePresenter
{
    private BanEntity $ban;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly BanFormFactory $banFormFactory,
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

    protected function createComponentBanForm() : BootstrapForm
    {
        return $this->banFormFactory->create($this->ban, $this->admin(), function (BanEntity $ban) : void {
            $this->adminFlash($this->translator->translate('admin-ban-edit.success', ['id' => $ban->id]));
            $this->redirect(':Admin:Ban:List:default');
        });
    }
}
