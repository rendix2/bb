<?php

namespace App\UI\Forum\Index;

use App\Database\EntityManagerDecorator;
use App\ForumModule\Presenters\Base\ForumPresenter as BaseForumPresenter;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use App\Models\CategoryManager;
use App\Models\Crud\CrudNullManager;
use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Models\ModeratorManager;

/**
 * Description of IndexPresenter
 *
 * @author rendix2
 * @method CategoryManager getManager()
 * @package App\ForumModule\Presenters
 */
class IndexPresenter extends BaseForumPresenter
{
    /**
     *
     * @var ModeratorManager $moderatorManager
     * @inject
     */
    public $moderatorsManager;

    /**
     * IndexPresenter constructor.
     *
     * @param CrudNullManager $crudNullManager
     * @param EntityManagerDecorator $em
     */
    public function __construct(
        CrudNullManager $crudNullManager,
        private readonly EntityManagerDecorator $em,
    )
    {
        parent::__construct($crudNullManager);
    }

    /**
     * IndexPresenter destructor.
     */
    public function __destruct()
    {
        $this->forumsManager     = null;
        $this->topicsManager     = null;
        $this->postsManager      = null;
        $this->moderatorsManager = null;

        parent::__destruct();
    }

    /**
     * renders index page
     */
    public function renderDefault(): void
    {
        $categories = $this
            ->em
            ->getRepository(CategoryEntity::class)
            ->findBy(
                [
                    'active' => true,
                ],
                [
                    'order' => 'ASC'
                ]
            );

        $result     = [];
        $result['cats'] = [];

        if ($this->getUser()->getIdentity()) {
            $last_login_time = $this->getUser()->getIdentity()->getData()['user_last_login_time'];
        } else {
            // we do not show any new posts
            $last_login_time = time() + 1;
        }

        foreach ($categories as $category) {
            $category->forums = [];

            $forums = $this->em
                ->getRepository(ForumEntity::class)
                ->findBy(
                    [
                        'category' => $category,
                        'active' => true,
                        'parent_id' => null,
                    ],
                    [
                        'order' => 'ASC'
                    ]
                );

            $result['cats'][$category->category_id] = $category;

            foreach ($forums as $forum) {
                $category->forums[$forum->forum_id] = $forum;
                $forum->moderators                  = [];
                $result['cats'][$category->category_id]->forums[$forum->forum_id] = $forum;

                $forum->hasNewPosts  = count(
                    $this->postsManager->getNewerPosts($forum->forum_id, $last_login_time)
                );

                $forum->hasNewTopics = count(
                    $this->topicsManager->getNewerTopicsCached($forum->forum_id, $last_login_time)
                );

                $moderators = $this->moderatorsManager->getAllByRightJoined($forum->forum_id);

                foreach ($moderators as $moderator) {
                    unset($moderator->user_password);

                    $result['cats'][$category->category_id]->forums[$forum->forum_id]->moderators[$moderator->user_id] = $moderator;
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

        $lastTopic = $this->em
            ->getRepository(TopicEntity::class)
            ->findOneBy([], ['id' => 'DESC']);

        $lastPost = $this->em
            ->getRepository(PostEntity::class)
            ->findOneBy([], ['id' => 'DESC']);

        $lastUser = $this->em
            ->getRepository(UserEntity::class)
            ->findOneBy([], ['id' => 'DESC']);

        $totalUserCount = $this->em
            ->getRepository(UserEntity::class)
            ->count();

        $totalTopicCount = $this->em
            ->getRepository(TopicEntity::class)
            ->count();

        $totalPostCount = $this->em
            ->getRepository(PostEntity::class)
            ->count();

        $mostPostUser = $this->em
            ->getRepository(PostEntity::class)
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

        $mostTopicUser = $this->em
            ->getRepository(TopicEntity::class)
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
    }
}
