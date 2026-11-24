<?php declare(strict_types=1);

namespace App\UI\Admin\Category\List;

use App\UI\Admin\AdminBasePresenter;
use Contributte\Datagrid\Datagrid;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;

/**
 * The categories of the forum (slice 7a).
 */
class ListPresenter extends AdminBasePresenter
{
    public function __construct(
        private readonly CategoryDataGrid $categoryDataGrid,
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
        return $this->categoryDataGrid->create();
    }
}
