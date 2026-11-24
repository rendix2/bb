<?php declare(strict_types=1);

namespace App\UI\Admin\Rank\Edit;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\RankEntity;
use App\UI\Admin\Accessory\RankFormFactory;
use App\UI\Admin\AdminBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use Nette\Localization\Translator;

/**
 * Editing a rank.
 */
class EditPresenter extends AdminBasePresenter
{
    private RankEntity $rank;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly RankFormFactory $rankFormFactory,
        private readonly MenuComponentFactory $menuFactory,
        private readonly Translator $translator,
    ) {
        parent::__construct();
    }

    public function actionDefault(int $id) : void
    {
        $this->rank = $this->em->find(RankEntity::class, $id) ?? $this->error('Rank was not found.');
    }

    public function renderDefault() : void
    {
        $this->template->rank = $this->rank;
    }

    protected function createComponentMenu() : MenuComponent
    {
        return $this->menuFactory->create('admin');
    }

    protected function createComponentRankForm() : BootstrapForm
    {
        return $this->rankFormFactory->create($this->rank, function (RankEntity $rank) : void {
            $this->adminFlash($this->translator->translate('admin-rank-edit.success', ['name' => $rank->name]));
            $this->redirect(':Admin:Rank:List:default');
        });
    }
}
