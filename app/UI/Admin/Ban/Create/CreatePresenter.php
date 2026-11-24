<?php declare(strict_types=1);

namespace App\UI\Admin\Ban\Create;

use App\Model\Entity\BanEntity;
use App\UI\Admin\Accessory\BanFormFactory;
use App\UI\Admin\AdminBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use Nette\Localization\Translator;

/**
 * A new ban.
 */
class CreatePresenter extends AdminBasePresenter
{
    public function __construct(
        private readonly BanFormFactory $banFormFactory,
        private readonly MenuComponentFactory $menuFactory,
        private readonly Translator $translator,
    ) {
        parent::__construct();
    }

    protected function createComponentMenu() : MenuComponent
    {
        return $this->menuFactory->create('admin');
    }

    protected function createComponentBanForm() : BootstrapForm
    {
        return $this->banFormFactory->create(null, $this->admin(), function (BanEntity $ban) : void {
            $this->adminFlash($this->translator->translate('admin-ban-create.success', ['id' => $ban->id]));
            $this->redirect(':Admin:Ban:List:default');
        });
    }
}
