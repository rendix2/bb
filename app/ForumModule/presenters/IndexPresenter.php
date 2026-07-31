<?php

namespace App\UI\Forum\Index;

use App\Database\EntityManagerDecorator;
use App\ForumModule\Presenters\Base\ForumPresenter as BaseForumPresenter;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use App\Model\Repository\CategoryRepository;
use App\Model\Repository\ForumRepository;
use App\Model\Repository\PostRepository;
use App\Model\Repository\TopicRepository;
use App\Model\Repository\UserRepository;
use App\Models\Crud\CrudNullManager;
use App\Models\ModeratorManager;
use Nette\DI\Attributes\Inject;

/**
 * Description of IndexPresenter
 *
 * @author rendix2
 * @package App\ForumModule\Presenters
 */
class IndexPresenter extends BaseForumPresenter
{
    #[Inject]
    public ModeratorManager $moderatorsManager;

    public function __construct(
        CrudNullManager $crudNullManager,
        private readonly EntityManagerDecorator $em,

        private readonly CategoryRepository $categoryRepository,
        private readonly ForumRepository    $forumRepository,
        private readonly TopicRepository    $topicRepository,
        private readonly PostRepository     $postRepository,

        private readonly UserRepository $userRepository,
    )
    {
        parent::__construct($crudNullManager);
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

        $result     = [];
        $result['cats'] = [];

        if ($this->getUser()->getIdentity()) {
            $last_login_time = $this->getUser()->getIdentity()->getData()['user_last_login_time'];
        } else {
            // we do not show any new posts
            $last_login_time = new \DateTimeImmutable();
        }

        foreach ($categories as $category) {
            $forums = $this->forumRepository
                ->findBy(
                    [
                        'category' => $category,
                        'active' => true,
                        'parent' => null,
                    ],
                    [
                        'sortOrder' => 'ASC'
                    ]
                );

            $result['cats'][$category->id] = $category;

            foreach ($forums as $forum) {
                $category->forums[$forum->id] = $forum;
                $result['cats'][$category->id]->forums[$forum->id] = $forum;

                $forum->hasNewPosts  = count(
                    $this->postRepository->findNewerPosts($forum->id, $last_login_time)
                );

                $forum->hasNewTopics = count(
                    $this->topicRepository->findNewerTopics($forum->id, $last_login_time)
                );

                //$moderators = $this->moderatorsManager->getAllByRightJoined($forum->id);

                $moderators = $forum->moderatorUsers;

                foreach ($moderators as $moderator) {
                    unset($moderator->user_password);

                    $result['cats'][$category->id]->forums[$forum->id]->moderators[$moderator->user_id] = $moderator;
                }
            }
        }

        /*

        $cachedLastUser = $this->getCache()
            ->load(self::CACHE_KEY_LAST_USER);

        if (!$cachedLastUser) {
            $this->getCache()
                ->save(
                    self::CACHE_KEY_LAST_USER,
                    //$cachedLastUser = $this->usersManager->getLast(),
                    [
                        Cache::EXPIRE => '1 hour',
                    ]
                );
        }

        $cachedLastTopic = $this->getCache()
            ->load(self::CACHE_KEY_LAST_TOPIC);

        if (!$cachedLastTopic) {
            $this->getCache()
                ->save(
                    self::CACHE_KEY_LAST_TOPIC,
                    $cachedLastTopic = $this->topicsManager->getLast(),
                    [
                        Cache::EXPIRE => '1 hour',
                    ]
                );
        }

        $cachedLastPost = $this->getCache()
            ->load(self::CACHE_KEY_LAST_POST);

        if (!$cachedLastPost) {
            $this->getCache()
                ->save(
                    self::CACHE_KEY_LAST_POST,
                    $cachedLastPost = $this->postsManager->getLast(),
                    [
                        Cache::EXPIRE => '1 hour',
                    ]
                );
        }
        */

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

        bdump($result);

        $this->template->mostPostsUser  = $mostPostUser;
        $this->template->mostTopicsUser = $mostTopicUser;
        $this->template->lastTopic      = $lastTopic;
        $this->template->lastUser       = $lastUser;
        $this->template->lastPost       = $lastPost;
        $this->template->totalUsers     = $totalUserCount;
        $this->template->totalPosts     = $totalPostCount;
        $this->template->totalTopics    = $totalTopicCount;
        $this->template->data           = $result;

        $this->template->categories = $rootCategories;
    }
}
