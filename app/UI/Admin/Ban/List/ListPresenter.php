<?php declare(strict_types=1);

namespace App\UI\Admin\Ban\List;

use App\UI\Admin\AdminBasePresenter;
use Contributte\Datagrid\Datagrid;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;

/**
 * The bans (slice 7c1).
 */
class ListPresenter extends AdminBasePresenter
{
    public function __construct(
        private readonly BanDataGrid $banDataGrid,
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
        return $this->banDataGrid->create();
    }
}
