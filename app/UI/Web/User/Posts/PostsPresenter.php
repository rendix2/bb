<?php declare(strict_types=1);

namespace App\UI\Web\User\Posts;

use App\Database\EntityManagerDecorator;
use App\Model\Service\TopicPaginationService;
use App\Model\Service\UserProfileService;
use App\Settings\ForumSettings;
use App\UI\Web\User\Accessory\ProfilePresenter;
use Nette\Utils\Paginator;

/**
 * The posts of a user, newest first, paginated: only from the forums the viewer may read (the old list showed also hidden forums).
 */
class PostsPresenter extends ProfilePresenter
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
        // as many as the topics of a forum page
        $paginator->setItemsPerPage(max(1, (int) $this->forumSettings->get()['pagination']['itemsPerPage']));
        $paginator->setPage($page);

        $result = $this->userProfileService->postsPage($this->userEntity, $this->viewer, $paginator->getOffset(), $paginator->getLength());
        $paginator->setItemCount($result['count']);

        $this->getTemplate()->userEntity = $this->userEntity;
        $this->getTemplate()->items = $result['items'];
        $this->getTemplate()->paginator = $paginator;

        $pages = [];

        foreach ($result['items'] as $post) {
            $pages[$post->id] = $this->topicPagination->pageOf($post);
        }

        // the page of the topic where each post is (the link goes right to it)
        $this->getTemplate()->pages = $pages;
    }
}
