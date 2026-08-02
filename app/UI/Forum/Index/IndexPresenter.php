<?php

namespace App\UI\Forum\Index;

use App\ForumModule\Presenters\Base\ForumPresenter as BaseForumPresenter;
use App\Model\Repository\CategoryRepository;
use App\Model\Repository\PostRepository;
use App\Model\Repository\TopicRepository;
use App\Model\Repository\UserRepository;
use App\Models\Crud\CrudNullManager;
use App\Models\ModeratorManager;
use Nette\Application\UI\Presenter;
use Nette\DI\Attributes\Inject;

/**
 * Description of IndexPresenter
 *
 * @author rendix2
 * @package App\ForumModule\Presenters
 */
class IndexPresenter extends Presenter
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly TopicRepository    $topicRepository,
        private readonly PostRepository     $postRepository,

        private readonly UserRepository $userRepository,
    )
    {
        parent::__construct();
    }

    /**
     * renders index page
     */
    public function renderDefault(): void
    {
        $categories = $this->categoryRepository
            ->findBy(
                [
                    'active' => true,
                ],
                [
                    'sortOrder' => 'ASC'
                ]
            );

        $rootCategories = $this->categoryRepository
            ->findBy(
                [
                    'active' => true,
                    'parent' => null,
                ],
                [
                    'sortOrder' => 'ASC'
                ]
            );

        if ($this->getUser()->getIdentity()) {
            $last_login_time = $this->getUser()->getIdentity()->getData()['user_last_login_time'];
        } else {
            // we do not show any new posts
            $last_login_time = new \DateTimeImmutable();
        }

        foreach ($categories as $category) {
            foreach ($category->forums as $forum) {
                $category->forums[$forum->id] = $forum;

                $forum->hasNewPosts  = count(
                    $this->postRepository->findNewerPosts($forum->id, $last_login_time)
                );

                $forum->hasNewTopics = count(
                    $this->topicRepository->findNewerTopics($forum->id, $last_login_time)
                );
            }
        }

        $lastTopic = $this->topicRepository
            ->findOneBy([], ['id' => 'DESC']);

        $lastPost = $this->postRepository
            ->findOneBy([], ['id' => 'DESC']);

        $lastUser = $this->userRepository
            ->findOneBy([], ['id' => 'DESC']);

        $totalUserCount = $this->userRepository
            ->count();

        $totalTopicCount = $this->topicRepository
            ->count();

        $totalPostCount = $this->postRepository
            ->count();

        $mostPostUser = $this->postRepository
            ->createQueryBuilder('_p')

            ->select('COUNT(_p.id) AS post_count')
            ->addSelect('_u.id')
            ->addSelect('_u.username')

            ->innerJoin('_p.user', '_u')

            ->groupBy('_u.id')
            ->orderBy('COUNT(_p.id)', 'DESC')

            ->setMaxResults(1)

            ->getQuery()
            ->getOneOrNullResult();

        $mostTopicUser = $this->topicRepository
            ->createQueryBuilder('_t')

            ->select('COUNT(_t.id) AS topic_count')
            ->addSelect('_u.id')
            ->addSelect('_u.username')

            ->innerJoin('_t.user', '_u')

            ->groupBy('_u.id')
            ->orderBy('COUNT(_t.id)', 'DESC')

            ->setMaxResults(1)

            ->getQuery()
            ->getOneOrNullResult();

        $this->template->mostPostsUser  = $mostPostUser;
        $this->template->mostTopicsUser = $mostTopicUser;
        $this->template->lastTopic      = $lastTopic;
        $this->template->lastUser       = $lastUser;
        $this->template->lastPost       = $lastPost;
        $this->template->totalUsers     = $totalUserCount;
        $this->template->totalPosts     = $totalPostCount;
        $this->template->totalTopics    = $totalTopicCount;

        $this->template->categories = $rootCategories;
    }
}
