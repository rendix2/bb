<?php

namespace App\ForumModule\Presenters;

use App\ForumModule\Presenters\Base\ForumPresenter as BaseForumPresenter;
use App\Models\CategoriesManager;
use App\Models\ModeratorsManager;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;

/**
 * Description of IndexPresenter¨
 *
 * @author rendix2
 * @method CategoriesManager getManager()
 */
class IndexPresenter extends BaseForumPresenter
{
    //use \App\Models\Traits\ForumsTrait;
    //use \App\Models\Traits\TopicsTrait;
    //use \App\Models\Traits\PostTrait;
    use \App\Models\Traits\UsersTrait;
    
    /**
     * @var string
     */
    const CACHE_KEY_LAST_USER = 'lastUser';

    /**
     * @var string
     */
    const CACHE_KEY_TOTAL_USERS = 'totalUsers';

    /**
     * @var string
     */
    const CACHE_KEY_TOTAL_TOPICS = 'totalTopics';

    /**
     * @var string
     */
    const CACHE_KEY_TOTAL_POSTS = 'totalPosts';

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
     * @var ModeratorsManager $moderatorManager
     * @inject
     */
    public $moderatorManager;

    /**
     * IndexPresenter constructor.
     *
     * @param CategoriesManager $categoriesManager
     * @param IStorage          $storage
     */
    public function __construct(CategoriesManager $categoriesManager, IStorage $storage)
    {
        parent::__construct($categoriesManager);
        
        $this->cache = new Cache($storage, self::CACHE_NAMESPACE);
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->forumsManager    = null;
        $this->topicsManager    = null;
        $this->postsManager     = null;
        $this->usersManager     = null;
        $this->cache            = null;
        $this->moderatorManager = null;

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
    public function renderDefault()
    {
        $categories      = $this->getManager()->getActiveCategoriesCached();
        $result          = [];

        if ($this->getUser()->getIdentity()) {
            $last_login_time = $this->getUser()->getIdentity()->getData()['user_last_login_time'];
        } else {
            // we do not show any new posts
            $last_login_time = time() + 1;
        }

        foreach ($categories as $category) {
            $category->forums = [];
            $forums           = $this->forumsManager->getForumsFirstLevel($category->category_id);

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

                $moderators = $this->moderatorManager->getAllJoinedByRight($forum->forum_id);

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
                    $cachedLastUser = $this->usersManager->getLast(),
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

        $this->template->mostPostsUser  = $this->postsManager->getUserWithMostPosts();
        $this->template->mostTopicsUser = $this->topicsManager->getUserWithMostTopic();
        $this->template->lastTopic      = $cachedLastTopic;
        $this->template->lastUser       = $cachedLastUser;
        $this->template->lastPost       = $cachedLastPost;
        $this->template->totalUsers     = $this->usersManager->getCountCached();
        $this->template->totalPosts     = $this->postsManager->getCountCached();
        $this->template->totalTopics    = $this->topicsManager->getCountCached();
        $this->template->data           = $result;
    }
}
