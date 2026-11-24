<?php declare(strict_types=1);

namespace App\UI\Admin\Mail\List;

use App\Model\Service\MassMailService;
use App\UI\Admin\AdminBasePresenter;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;

/**
 * The history of the mass e-mails (slice 7e4), the newest first.
 */
class ListPresenter extends AdminBasePresenter
{
    private const int Limit = 100;

    public function __construct(
        private readonly MassMailService $massMailService,
        private readonly MenuComponentFactory $menuFactory,
    ) {
        parent::__construct();
    }

    public function renderDefault() : void
    {
        $this->template->massMails = $this->massMailService->history(self::Limit);
        $this->template->queueCount = $this->massMailService->queueCount();
    }

    protected function createComponentMenu() : MenuComponent
    {
        return $this->menuFactory->create('admin');
    }
}
