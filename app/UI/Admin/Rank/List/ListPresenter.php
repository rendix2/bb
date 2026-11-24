<?php declare(strict_types=1);

namespace App\UI\Admin\Rank\List;

use App\UI\Admin\AdminBasePresenter;
use Contributte\Datagrid\Datagrid;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;

/**
 * The ranks (slice 7d).
 */
class ListPresenter extends AdminBasePresenter
{
    public function __construct(
        private readonly RankDataGrid $rankDataGrid,
        private readonly MenuComponentFactory $menuFactory,
    ) {
        parent::__construct();
    }

    protected function createComponentMenu() : MenuComponent
    {
        return $this->menuFactory->create('admin');
    }

    protected function createComponentGrid() : Datagrid
    {
        return $this->rankDataGrid->create();
    }
}
