<?php declare(strict_types=1);

namespace App\UI\Admin\Avatar\List;

use App\Model\Service\UserAvatarService;
use App\UI\Admin\AdminBasePresenter;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use Nette\Utils\Paginator;

/**
 * The gallery of the avatars of users (slice 7e5), by the username, a link to delete each.
 */
class ListPresenter extends AdminBasePresenter
{
    private const int PerPage = 60;

    public function __construct(
        private readonly UserAvatarService $avatarService,
        private readonly MenuComponentFactory $menuFactory,
    ) {
        parent::__construct();
    }

    public function renderDefault(int $page = 1) : void
    {
        $paginator = new Paginator();
        $paginator->setItemsPerPage(self::PerPage);
        $paginator->setItemCount($this->avatarService->countUsersWithAvatar());
        $paginator->setPage($page);

        $this->template->users = $this->avatarService->usersWithAvatar($paginator->getOffset(), $paginator->getLength());
        $this->template->paginator = $paginator;
    }

    protected function createComponentMenu() : MenuComponent
    {
        return $this->menuFactory->create('admin');
    }
}
