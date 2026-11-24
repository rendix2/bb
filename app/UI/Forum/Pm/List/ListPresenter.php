<?php declare(strict_types=1);

namespace App\UI\Forum\Pm\List;

use App\Settings\ForumSettings;
use App\UI\Forum\Pm\Accessory\PrivateMessagePresenter;
use Nette\Utils\Paginator;

/**
 * My private message threads, the newest message first (unread and starred marked); only the threads I did not leave.
 */
class ListPresenter extends PrivateMessagePresenter
{
    private ForumSettings $forumSettings;

    public function injectForumSettings(ForumSettings $forumSettings) : void
    {
        $this->forumSettings = $forumSettings;
    }

    public function actionDefault(int $page = 1) : void
    {
        $this->loadViewer();
    }

    public function renderDefault(int $page = 1) : void
    {
        $paginator = new Paginator();
        $paginator->setItemsPerPage((int) $this->forumSettings->get()['pagination']['itemsPerPage']);
        $paginator->setItemCount($this->privateMessages->countThreadsOf($this->viewer));
        $paginator->setPage($page);

        $this->template->threads = $this->privateMessages->threadsOf($this->viewer, $paginator->getOffset(), $paginator->getLength());
        $this->template->paginator = $paginator;
    }
}
