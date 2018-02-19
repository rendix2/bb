<?php

namespace App\ForumModule\Presenters;

use App\Models\ForumsManager;
use App\Models\IndexManager;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;

/**
 * Description of IndexPresenter¨
 *
 * @author rendi
 * @method IndexManager getManager()
 */
class IndexPresenter extends Base\ForumPresenter
{

    /**
     *
     */
    const CACHE_KEY_LAST_USER = 'lastUser';
    /**
     *
     */
    const CACHE_KEY_TOTAL_USERS = 'totalUsers';
    /**
     *
     */
    const CACHE_KEY_TOTAL_TOPICS = 'totalTopics';
    /**
     *
     */
    const CACHE_KEY_TOTAL_POSTS = 'totalPosts';
    /**
     *
     */
    const CACHE_KEY_LAST_TOPIC = 'lastTopic';

    /**
     * @var Cache $cache
     */
    private $cache;

    /**
     * @var ForumsManager $forumsManager
     */
    private $forumsManager;
    
    private $topicManager;
        
    private $postManger;
    
    private $userManager;

    /**
     * IndexPresenter constructor.
     *
     * @param IndexManager $manager
     */
    public function __construct(IndexManager $manager)
    {
        parent::__construct($manager);
    }

    /**
     * @return mixed
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param IStorage $storage
     */
    public function injectCache(IStorage $storage)
    {
        $this->cache = new Cache($storage, 'BBIndex');
    }

    /**
     * @param ForumsManager $forumsManager
     */
    public function injectForumManager(ForumsManager $forumsManager)
    {
        $this->forumsManager = $forumsManager;
    }
    
    public function injectTopicManager(\App\Models\TopicsManager $topicManager){
        $this->topicManager = $topicManager;
    }
    
    public function injectPostManager(\App\Models\PostsManager $postManager){
        $this->postManger = $postManager;
    }
    
    public function injectUserManager(\App\Models\UsersManager $userManager){
        $this->userManager = $userManager;
    }

    /**
     * @param $category_id
     */
    public function renderCategory($category_id)
    {
        $forums = $this->getManager()->getForumByCategoryId($category_id);

        if (!$forums) {
            $this->flashMessage('No forums in this category.', self::FLASH_MESSAGE_DANGER);
        }

        $this->template->forums = $this->forumsManager->createForums($forums, 0);
    }

    /**
     *
     */
    public function renderDefault()
    {
        $categories = $this->getManager()->getActiveCategories();
        $result     = [];
        $last_login_time = $this->getUser()->getIdentity()->getData()['user_last_login_time'];

        foreach ($categories as $category) {
            $forums = $this->getManager()->getForumsFirstLevel($category->category_id);
                        
            foreach ($forums as $forum){
                $forum->hasNewPosts = count($this->postManger->getNewerPosts($forum->forum_id, $last_login_time));
                $forum->hasNewTopics = count($this->topicManager->getNewerTopics($forum->forum_id, $last_login_time));
            }
                                  
            $result[$category->category_id]['category'] = $category;
            $result[$category->category_id]['forum']    = $forums;
        }

        $cachedLastUser = $this->getCache()->load(self::CACHE_KEY_LAST_USER);

        if (!$cachedLastUser) {
            $this->getCache()->save(self::CACHE_KEY_LAST_USER, $cachedLastUser = $this->userManager->getLastUser(), [
                Cache::EXPIRE => '1 hour',
            ]);
        }

        $cachedLastTopic = $this->getCache()->load(self::CACHE_KEY_LAST_TOPIC);

        if (!$cachedLastTopic) {
            $this->getCache()->save(self::CACHE_KEY_LAST_TOPIC, $cachedLastTopic = $this->topicManager->getLastTopic(), [
                Cache::EXPIRE => '1 hour',
            ]);
        }

        $this->template->mostPostsUser = $this->getManager()->getUserWithMostPosts();
        $this->template->lastTopic   = $cachedLastTopic;
        $this->template->lastUser    = $cachedLastUser;
        $this->template->totalUsers  = $this->userManager->getCountCached();
        $this->template->totalPosts  = $this->postManger->getCountCached();
        $this->template->totalTopics = $this->topicManager->getCountCached();
        $this->template->data        = $result;
    }

}
