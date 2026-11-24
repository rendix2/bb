<?php declare(strict_types=1);

namespace App\UI\Admin\Rank\Create;

use App\Model\Entity\RankEntity;
use App\UI\Admin\Accessory\RankFormFactory;
use App\UI\Admin\AdminBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use Nette\Localization\Translator;

/**
 * A new rank.
 */
class CreatePresenter extends AdminBasePresenter
{
    public function __construct(
        private readonly RankFormFactory $rankFormFactory,
        private readonly MenuComponentFactory $menuFactory,
        private readonly Translator $translator,
    ) {
        parent::__construct();
    }

    protected function createComponentMenu() : MenuComponent
    {
        return $this->menuFactory->create('admin');
    }

    protected function createComponentRankForm() : BootstrapForm
    {
        return $this->rankFormFactory->create(null, function (RankEntity $rank) : void {
            $this->adminFlash($this->translator->translate('admin-rank-create.success', ['name' => $rank->name]));
            $this->redirect(':Admin:Rank:List:default');
        });
    }
}
