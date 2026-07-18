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
use App\Models\Traits\UsersTrait;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;

/**
 * Description of IndexPresenter
 *
 * @author rendix2
 * @method CategoryManager getManager()
 * @package App\ForumModule\Presenters
 */
class IndexPresenter extends BaseForumPresenter
{
    use UsersTrait;
    
    /**
     * @var string
     */
    const CACHE_KEY_LAST_USER = 'lastUser';

    /**
     * @var string
     */
    const CACHE_KEY_LAST_TOPIC = 'lastTopic';

    /**
     * @var string
     */
    const CACHE_KEY_LAST_POST = 'lastPost';

    /**
     * @var string
     */
    const CACHE_NAMESPACE = 'BBIndex';

    /**
     * @var Cache $cache
     */
    private $cache;

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
     * @param IStorage $storage
     */
    public function __construct(
        CrudNullManager $crudNullManager,
        private readonly EntityManagerDecorator $em,

        //CategoriesManager $categoriesManager,
        IStorage          $storage,
    )
    {
        parent::__construct($crudNullManager);
        
        $this->cache = new Cache($storage, self::CACHE_NAMESPACE);
    }

    /**
     * IndexPresenter destructor.
     */
    public function __destruct()
    {
        $this->forumsManager     = null;
        $this->topicsManager     = null;
        $this->postsManager      = null;
        $this->usersManager      = null;
        $this->cache             = null;
        $this->moderatorsManager = null;

        parent::__destruct();
    }

    /**
     * @return Cache
     */
    public function getCache()
    {
        return $this->cache;
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

        //$categories = $this->getManager()->getActiveCategoriesCached();
        $result     = [];

        if ($this->user->identity) {
            $last_login_time = $this->user->identity->getData()['user_last_login_time'];
        } else {
            // we do not show any new posts
            $last_login_time = time() + 1;
        }

        foreach ($categories as $category) {
            $category->forums = [];
            //$forums           = $this->forumsManager->getAllForumsFirstLevel($category->category_id);

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

        $totalUserCount = $this->em
            ->getRepository(UserEntity::class)
            ->count();

        $totalTopicCount = $this->em
            ->getRepository(TopicEntity::class)
            ->count();

        $totalPostCount = $this->em
            ->getRepository(PostEntity::class)
            ->count();

        $this->template->mostPostsUser  = $this->postsManager->getUserWithMostPosts();
        $this->template->mostTopicsUser = $this->topicsManager->getUserWithMostTopic();
        $this->template->lastTopic      = $cachedLastTopic;
        $this->template->lastUser       = $cachedLastUser;
        $this->template->lastPost       = $cachedLastPost;
        $this->template->totalUsers     = $totalUserCount;
        $this->template->totalPosts     = $totalPostCount;
        $this->template->totalTopics    = $totalTopicCount;
        $this->template->data           = $result;
    }
}
