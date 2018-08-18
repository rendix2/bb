<?php

namespace App\ForumModule\Presenters;

use App\Models\CategoriesManager;
use App\Models\ForumsManager;
use App\Models\IndexManager;
use App\Models\ModeratorsManager;
use App\Models\PostsManager;
use App\Models\TopicsManager;
use App\Models\UsersManager;
use App\Controls\BreadCrumbControl;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use dibi;

/**
 * Description of IndexPresenter¨
 *
 * @author rendix2
 * @method IndexManager getManager()
 */
class IndexPresenter extends Base\ForumPresenter
{
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
     * @var Cache $cache
     */
    private $cache;

    /**
     * @var ForumsManager $forumsManager
     * @inject
     */
    public $forumsManager;

    /**
     * @var TopicsManager $topicManager
     * @inject
     */
    public $topicManager;

    /**
     * @var PostsManager $postManger
     * @inject
     */
    public $postManger;

    /**
     * @var UsersManager $userManager
     * @inject
     */
    public $userManager;
    
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
     */
    public function __construct(CategoriesManager $categoriesManager)
    {
        parent::__construct($categoriesManager);
    }
    
    /**
     * @return Cache
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
     * renders categories
     *
     * @param int $category_id
     */
    public function renderCategory($category_id)
    {
        $forums = $this->forumsManager
                ->getByCategory($category_id)
                ->orderBy('forum_order', dibi::ASC)
                ->fetchAll();

        if (!$forums) {
            $this->flashMessage('No forums in this category.', self::FLASH_MESSAGE_DANGER);
        }

        $this->template->forums = $this->forumsManager->createForums($forums, 0);
    }

    /**
     * renders index page
     */
    public function renderDefault()
    {
        $categories      = $this->getManager()->getActiveCategoriesCached();
        $result          = [];
        $last_login_time = $this->getUser()->getIdentity()->getData()['user_last_login_time'];
      
        foreach ($categories as $category) {
            $category->forums = [];
            $forums           = $this->forumsManager->getForumsFirstLevel($category->category_id);
            
            $result['cats'][$category->category_id] = $category;

            foreach ($forums as $forum) {
                $category->forums[$forum->forum_id] = $forum;
                $forum->moderators                  = [];
                $result['cats'][$category->category_id]->forums[$forum->forum_id] = $forum;
                
                $forum->hasNewPosts  = count(
                    $this->postManger->getNewerPosts($forum->forum_id, $last_login_time)
                );
                
                $forum->hasNewTopics = count(
                    $this->topicManager->getNewerTopics($forum->forum_id, $last_login_time)
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
                    $cachedLastUser = $this->userManager->getLastUser(),
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
                    $cachedLastTopic = $this->topicManager->getLastTopic(),
                    [
                        Cache::EXPIRE => '1 hour',
                    ]
                );
        }

        $this->template->mostPostsUser = $this->postManger->getUserWithMostPosts();
        $this->template->lastTopic     = $cachedLastTopic;
        $this->template->lastUser      = $cachedLastUser;
        $this->template->totalUsers    = $this->userManager->getCountCached();
        $this->template->totalPosts    = $this->postManger->getCountCached();
        $this->template->totalTopics   = $this->topicManager->getCountCached();
        $this->template->data          = $result;
    }
    
    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbCategory()
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['text' => 'menu_category']
        ];
        
        return new BreadCrumbControl($breadCrumb, $this->getForumTranslator());
    }     
}
