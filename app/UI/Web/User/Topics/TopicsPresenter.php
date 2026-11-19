<?php declare(strict_types=1);

namespace App\UI\Web\User\Topics;

use App\Database\EntityManagerDecorator;
use App\Model\Service\UserProfileService;
use App\Settings\ForumSettings;
use App\UI\Web\User\Accessory\ProfilePresenter;
use Nette\Utils\Paginator;

/**
 * The topics of a user, newest first, paginated: only from the forums the viewer may read (the old list showed also hidden forums).
 */
class TopicsPresenter extends ProfilePresenter
{
    public function __construct(
        EntityManagerDecorator $em,
        private readonly UserProfileService $userProfileService,
        private readonly ForumSettings $forumSettings,
    )
    {
        parent::__construct($em);
    }

    public function actionDefault(?string $uuid = null, int $page = 1) : void
    {
        $this->loadProfile($uuid);
    }

    public function renderDefault(?string $uuid = null, int $page = 1) : void
    {
        $paginator = new Paginator();
        // as many as the topics of a forum page
        $paginator->setItemsPerPage(max(1, (int) $this->forumSettings->get()['pagination']['itemsPerPage']));
        $paginator->setPage($page);

        $result = $this->userProfileService->topicsPage($this->userEntity, $this->viewer, $paginator->getOffset(), $paginator->getLength());
        $paginator->setItemCount($result['count']);

        $this->getTemplate()->userEntity = $this->userEntity;
        $this->getTemplate()->items = $result['items'];
        $this->getTemplate()->paginator = $paginator;
    }
}
