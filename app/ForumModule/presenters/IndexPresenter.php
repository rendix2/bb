<?php

namespace App\ForumModule\Presenters;

use Nette\Caching\Cache;

/**
 * Description of IndexPresenter¨
 *
 * @author rendi
 * @method \App\Models\IndexManager getManager()   
 */
class IndexPresenter extends Base\ForumPresenter {

    const CACHE_KEY_LAST_USER = 'lastUser';
    const CACHE_KEY_TOTAL_USERS = 'totalUsers';
    const CACHE_KEY_TOTAL_TOPICS = 'totalTopics';
    const CACHE_KEY_TOTAL_POSTS = 'totalPosts';
    const CACHE_KEY_LAST_TOPIC = 'lastTopic';

    private $cache;
    
    private $forumsManager;

    public function __construct(\App\Models\IndexManager $manager) {
        parent::__construct($manager);
    }

    public function injectCache(\Nette\Caching\IStorage $storage) {
        $this->cache = new Cache($storage, 'BBIndex');
    }
    
    public function inject(\App\Models\ForumsManager $forumsManager){
        $this->forumsManager = $forumsManager;
    }

    public function getCache() {
        return $this->cache;
    }

    public function renderDefault() {
        $categories = $this->getManager()->getActiveCategories();
        $result = [];

        foreach ($categories as $category) {
            $forums = $this->getManager()->getForumsFirstLevel($category->category_id);

            $result[$category->category_id]['category'] = $category;
            $result[$category->category_id]['forum'] = $forums;
        }

        $cachedLastUser = $this->getCache()->load(self::CACHE_KEY_LAST_USER);

        if (!$cachedLastUser) {
            $this->getCache()->save(self::CACHE_KEY_LAST_USER, $cachedLastUser = $this->getManager()->getLastUser(), [
                Cache::EXPIRE => '1 hour',
            ]);
        }

        $cachedTotalUsers = $this->getCache()->load(self::CACHE_KEY_TOTAL_USERS);

        if (!$cachedTotalUsers) {
            $this->getCache()->save(self::CACHE_KEY_TOTAL_USERS, $cachedTotalUsers = $this->getManager()->getTotalUsers(), [
                Cache::EXPIRE => '1 hour',
            ]);
        }

        $cachedTotalPosts = $this->getCache()->load(self::CACHE_KEY_TOTAL_POSTS);

        if (!$cachedTotalPosts) {
            $this->getCache()->save(self::CACHE_KEY_TOTAL_POSTS, $cachedTotalPosts = $this->getManager()->getTotalPosts(), [
                Cache::EXPIRE => '1 hour',
            ]);
        }

        $cachedTotalTopics = $this->getCache()->load(self::CACHE_KEY_TOTAL_TOPICS);

        if (!$cachedTotalTopics) {
            $this->getCache()->save(self::CACHE_KEY_TOTAL_TOPICS, $cachedTotalTopics = $this->getManager()->getTotalTopics(), [
                Cache::EXPIRE => '1 hour',
            ]);
        }


        $cachedLastTopic = $this->getCache()->load(self::CACHE_KEY_LAST_TOPIC);

        if (!$cachedLastTopic) {
            $this->getCache()->save(self::CACHE_KEY_LAST_TOPIC, $cachedLastTopic = $this->getManager()->getLastTopic(), [
                Cache::EXPIRE => '1 hour',
            ]);
        }

        $this->template->lastTopic = $cachedLastTopic;
        $this->template->lastUser = $cachedLastUser;
        $this->template->totalUsers = $cachedTotalUsers;
        $this->template->totalPosts = $cachedTotalPosts;
        $this->template->totalTopics = $cachedTotalTopics;
        $this->template->data = $result;
    }

    public function renderCategory($category_id) {
        $forums = $this->getManager()->getForumByCategoryId($category_id);

        if (!$forums) {
            $this->flashMessage('No forums in this category.', self::FLASH_MESSAGE_DANGER);
        }
        
        $this->template->forums = $this->forumsManager->createForums($forums, 0);
    }

}
