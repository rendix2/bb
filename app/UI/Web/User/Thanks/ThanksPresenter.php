<?php declare(strict_types=1);

namespace App\UI\Web\User\Thanks;

use App\Database\EntityManagerDecorator;
use App\Model\Service\TopicPaginationService;
use App\Model\Service\UserProfileService;
use App\Settings\ForumSettings;
use App\UI\Web\User\Accessory\ProfilePresenter;
use Nette\Utils\Paginator;

/**
 * The thanks a user received (decision of 2026-11-20): who thanked him and for what (his topic or his post), the newest first,
 * paginated, for logged-in users on his profile; only from the forums the viewer may read (UserProfileService, like the count on
 * the profile).
 */
class ThanksPresenter extends ProfilePresenter
{
    public function __construct(
        EntityManagerDecorator $em,
        private readonly UserProfileService $userProfileService,
        private readonly ForumSettings $forumSettings,
        private readonly TopicPaginationService $topicPagination,
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
        $paginator->setItemsPerPage(max(1, (int) $this->forumSettings->get()['pagination']['itemsPerPage']));
        $paginator->setPage($page);

        $result = $this->userProfileService->thanksPage($this->userEntity, $this->viewer, $paginator->getOffset(), $paginator->getLength());
        $paginator->setItemCount($result['count']);

        $pages = [];

        foreach ($result['items'] as $thank) {
            if ($thank->post !== null) {
                $pages[$thank->post->id] = $this->topicPagination->pageOf($thank->post);
            }
        }

        $this->getTemplate()->userEntity = $this->userEntity;
        $this->getTemplate()->items = $result['items'];
        $this->getTemplate()->paginator = $paginator;
        // the page of the topic where each thanked post is (the link goes right to it)
        $this->getTemplate()->pages = $pages;
    }
}
