<?php declare(strict_types=1);

namespace App\UI\Admin\Forum\List;

use App\UI\Admin\AdminBasePresenter;
use Contributte\Datagrid\Datagrid;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;

/**
 * The forums (slice 7a).
 */
class ListPresenter extends AdminBasePresenter
{
    public function __construct(
        private readonly ForumDataGrid $forumDataGrid,
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
        return $this->forumDataGrid->create();
    }
}
