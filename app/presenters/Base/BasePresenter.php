<?php

namespace App\Presenters\Base;

use App\Authorizator;
use App\Controls\BootstrapForm;
use App\Controls\MenuControl;
use App\Models\BansManager;
use App\Services\TranslatorFactory;
use Nette;
use Nette\Http\IResponse;
use Nextras\Application\UI\SecuredLinksPresenterTrait;
use App\Models\PostsManager;
use App\Models\TopicsManager;
use App\Models\ForumsManager;
use App\Models\CategoriesManager;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    use SecuredLinksPresenterTrait;
    
    /**
     * @var string
     */
    const FLASH_MESSAGE_SUCCESS = 'success';
    
    /**
     * @var string
     */
    const FLASH_MESSAGE_DANGER = 'danger';
    
    /**
     * @var string
     */
    const FLASH_MESSAGE_WARNING = 'warning';
    
    /**
     * @var string
     */
    const FLASH_MESSAGE_INFO = 'info';
    
    /**
     * @var string
     */
    const BECK_END_NAMESPACE = 'backend';
    
    /**
     * @var string
     */
    const FRONT_END_NAMESPACE = 'frontend';
    
    /**
     * @var string
     */
    const MODERATOR_END_SPACE = 'moderator';
    
    /**
     * @var BansManager $banManager
     * @inject
     */
    public $banManager;
      
    /**
     * @var BootstrapForm $bootStrapForm
     */
    private $bootstrapForm;

    /**
     * @var TranslatorFactory $translatorFactory
     * @inject
     */
    public $translatorFactory;
    
    /**
     * @var CategoriesManager $categoriesManager
     * @inject
     */
    public $categoriesManager;     
    
    /**
     * @var ForumsManager $forumsManager
     * @inject
     */
    public $forumsManager;  
    
    /**
     * @var TopicsManager $topicsManager
     * @inject
     */
    public $topicsManager;    
    
    /**
     *
     * @var PostsManager $manager
     * @inject
     */
    public $postsManager;
    



    /**
     * BasePresenter constructor.
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->bootstrapForm = BootstrapForm::create();
    }

    /**
     *
     */
    public function startup()
    {
        parent::startup();
             
        $this->banUser();

        $this->template->id          = $this->getParameter('id');
        $this->template->user_id     = $this->getParameter('user_id');
        $this->template->category_id = $this->getParameter('category_id');
        $this->template->forum_id    = $this->getParameter('forum_id');
        $this->template->topic_id    = $this->getParameter('topic_id');
        $this->template->post_id     = $this->getParameter('post_id');
        $this->template->page        = $this->getParameter('page');
    }
    
    /**
     * create BootstrapForm
     *
     * @return BootstrapForm
     */
    public function createBootstrapForm()
    {
        return new BootstrapForm();
    }
    
    /**
     * @return BootstrapForm
     */
    public function getBootstrapForm()
    {
        return $this->bootstrapForm;
    }
    
    /**
     * ban user
     */
    private function banUser()
    {
        $bans     = $this->banManager->getAllCached();
        $identity = $this->getUser()->getIdentity();
        $user     = $this->getUser();
        
        // if not main admin or role is not admin, so you can not ban admin, if some problem....
        if ($user->getId() !== 1 || !in_array(Authorizator::ROLES[5], $this->getUser()->getRoles(), true)) {
            foreach ($bans as $ban) {
                if ($identity && $this->getUser()->isLoggedIn()) {
                    if ($ban->ban_email === $identity->getData()['user_email'] || $ban->ban_user_name === $identity->getData()['user_name']) {
                        $this->error('Banned', IResponse::S403_FORBIDDEN);
                    }
                }

                if ($ban->ban_ip === $this->getHttpRequest()->getRemoteAddress()) {
                    $this->error('Banned', IResponse::S403_FORBIDDEN);
                }
            }
        }
    }

    /**
     * @return bool
     */
    protected function checkLoggedIn()
    {
        $identity = $this->getUser()->getIdentity();
        
        if (!$identity) {
            return false;
        }
        
        return $this->getUser()->isLoggedIn();
    }

    /**
     * @return bool
     */
    protected function checkUserLoggedIn()
    {
        return $this->checkAdminLoggedIn() && !in_array('guest', $this->getUser()->getRoles(), true);
    }

    /**
     * @return bool
     */
    protected function checkJuniorAdminLoggedIn()
    {
        return $this->checkLoggedIn() && $this->getUser()->isInRole('juniorAdmin');
    }

    /**
     * @return bool
     */
    protected function checkAdminLoggedIn()
    {
        return $this->checkLoggedIn() && $this->getUser()->isInRole('admin');
    }

    /**
     * @return MenuControl
     */
    protected function createComponentMenuAdmin()
    {
        $leftMenu = [
            0 => ['presenter' => ':Admin:Index:default', 'title' => 'menu_index'],
            1 => ['presenter' => ':Admin:Forum:default', 'title' => 'menu_forums'],
            2 => ['presenter' => ':Admin:Category:default', 'title' => 'menu_categories'],
            3 => ['presenter' => ':Admin:User:default', 'title' => 'menu_users'],
            4 => ['presenter' => ':Admin:Avatar:default', 'title' => 'menu_avatar'],
            5 => ['presenter' => ':Admin:Email:default', 'title' => 'menu_emails'],
            6 => ['presenter' => ':Admin:Cache:default', 'title' => 'menu_cache'],
            7 => ['presenter' => ':Admin:Language:default', 'title' => 'menu_language'],
            8 => ['presenter' => ':Admin:Group:default', 'title' => 'menu_groups'],
            9 => ['presenter' => ':Admin:Rank:default', 'title' => 'menu_ranks'],
            10 => ['presenter' => ':Admin:Report:default', 'title' => 'menu_reports'],
            11 => ['presenter' => ':Admin:Ban:default', 'title' => 'menu_bans'],
            /*12 => ['presenter' => ':Admin:Config:Index:default', 'title' => 'menu_config',
                'submenu' => [0 => ['presenter' => ':Admin:Config:Database:dumps', 'title' => 'menu_database']]
            ],*/
        ];

        $rightMenu = [
            0 => ['presenter' => 'logout', 'title' => 'logout'],
            1 => ['presenter' => ':Forum:Index:default', 'title' => 'menu_forum'],
        ];

        return new MenuControl($this->translatorFactory->adminTranslatorFactory(), $leftMenu, $rightMenu);
    }
    
    public function checkCategoryParam($category_id)
    {
        // category check
        if (!isset($category_id)) {
            $this->error('Category param is not set.');
        }

        if (!is_numeric($category_id)) {
            $this->error('Category param is not numeric.');
        }

        $category = $this->categoriesManager->getById($category_id);

        if (!$category) {
            $this->error('Category was not found.');
        }

        if (!$category->category_active) {
            $this->error('Category is not active.');
        }

        return $category;
    }
    
    public function checkForumParam($forum_id, $category_id)
    {
        // forum check
        if (!isset($forum_id)) {
            $this->error('Forum param is not set.');
        }

        if (!is_numeric($forum_id)) {
            $this->error('Forum param is not numeric.');
        }

        $forum = $this->forumsManager->getById($forum_id);

        if (!$forum) {
            $this->error('Forum was not found.');
        }

        if ($forum->forum_category_id !== (int)$category_id) {
            $this->error('Category param does not match.');
        }

        if (!$forum->forum_active) {
            $this->error('Forum is not active.');
        }

        return $forum;
    }    

    public function checkTopicParam($topic_id, $category_id, $forum_id)
    {
        // topic check
        if (!isset($topic_id)) {
            $this->error('Topic param is not set.');
        }

        if (!is_numeric($topic_id)) {
            $this->error('Topic param is not numeric.');
        }

        $topicDibi = $this->topicsManager->getById($topic_id);
        
        if (!$topicDibi) {
            $this->error('Topic was not found.');
        }
        
        $topic = \App\Models\Entity\Topic::get($topicDibi);

        if ($topic->topic_category_id !== (int)$category_id) {
            $this->error('Category param does not match.');
        }

        if ($topic->topic_forum_id !== (int)$forum_id) {
            $this->error('Forum param does not match.');
        }

        if ($topic->topic_locked) {
            $this->error('Topic is locked.', IResponse::S403_FORBIDDEN);
        }
        
        return $topic;
    }
    
    public function checkPostParam($post_id, $category_id, $forum_id, $topic_id)
    {
        if (!isset($post_id)) {
            $this->error('Post param is not set.');
        }
        
        if (!is_numeric($post_id)) {
            $this->error('Post param is not numeric.');
        }        

        $postDibi = $this->postsManager->getById($post_id);

        if (!$postDibi) {
            $this->error('Post was not found.');
        }
        
        $post = \App\Models\Entity\Post::get($postDibi);        

        if ($post->post_category_id !== (int)$category_id) {
            $this->error('Category param does not match.', IResponse::S403_FORBIDDEN);
        }

        if ($post->post_forum_id !== (int)$forum_id) {
            $this->error('Category param does not match.', IResponse::S403_FORBIDDEN);
        }

        if ($post->post_topic_id !== (int)$topic_id) {
            $this->error('Category param does not match.', IResponse::S403_FORBIDDEN);
        }

        if ($post->post_user_id !== $this->getUser()->getId()) {
            $this->error('You are not author of post.', IResponse::S403_FORBIDDEN);
        }

        if ($post->post_locked) {
            $this->error('Post is locked.', IResponse::S403_FORBIDDEN);
        }

        return $post;
    }
    

}
