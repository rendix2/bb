<?php declare(strict_types=1);

namespace App\UI\Admin\Mail\Queue;

use App\Model\Service\MassMailService;
use App\UI\Admin\AdminBasePresenter;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;

/**
 * The mail queue (slice 7e4): the mails not sent yet by the cron mail:send-queue, the oldest first.
 */
class QueuePresenter extends AdminBasePresenter
{
    private const int Limit = 200;

    public function __construct(
        private readonly MassMailService $massMailService,
        private readonly MenuComponentFactory $menuFactory,
    ) {
        parent::__construct();
    }

    public function renderDefault() : void
    {
        $this->template->mails = $this->massMailService->queue(self::Limit);
        $this->template->count = $this->massMailService->queueCount();
        $this->template->limit = self::Limit;
    }

    protected function createComponentMenu() : MenuComponent
    {
        return $this->menuFactory->create('admin');
    }
}
