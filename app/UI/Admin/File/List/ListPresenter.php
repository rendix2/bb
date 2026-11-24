<?php declare(strict_types=1);

namespace App\UI\Admin\File\List;

use App\Model\Service\AttachmentService;
use App\UI\Admin\AdminBasePresenter;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use Nette\Utils\Paginator;

/**
 * The attachments of posts (slice 7e5): the newest first, the total size, a link to delete each.
 */
class ListPresenter extends AdminBasePresenter
{
    private const int PerPage = 50;

    public function __construct(
        private readonly AttachmentService $attachmentService,
        private readonly MenuComponentFactory $menuFactory,
    ) {
        parent::__construct();
    }

    public function renderDefault(int $page = 1) : void
    {
        $totals = $this->attachmentService->totals();

        $paginator = new Paginator();
        $paginator->setItemsPerPage(self::PerPage);
        $paginator->setItemCount($totals['count']);
        $paginator->setPage($page);

        $this->template->files = $this->attachmentService->newest($paginator->getOffset(), $paginator->getLength());
        $this->template->totals = $totals;
        $this->template->paginator = $paginator;
    }

    protected function createComponentMenu() : MenuComponent
    {
        return $this->menuFactory->create('admin');
    }
}
