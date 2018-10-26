<?php

namespace App\ForumModule\Presenters;

use App\Controls\BreadCrumbControl;
use App\Controls\GridFilter;
use App\Controls\PaginatorControl;
use App\Forms\ForumSearchInForumForm;
use App\Models\CategoriesManager;
use App\Models\ForumsManager;
use App\Models\ModeratorsManager;
use App\Models\TopicsManager;
use App\Settings\ForumSettings;
use App\Settings\TopicsSetting;
use Nette\Http\IResponse;

/**
 * Description of ForumPresenter
 *
 * @author rendix2
 * @method ForumsManager getManager()
 */
final class ForumPresenter extends Base\ForumPresenter
{
    use \App\Models\Traits\CategoriesTrait;
    //use \App\Models\Traits\ForumsTrait;
    //use \App\Models\Traits\TopicsTrait;
    
    /**
     *
     * @var ForumSettings $forumsSettings
     * @inject
     */
    public $forumSettings;
    
    /**
     * @var TopicsSetting $topicSetting
     * @inject
     */
    public $topicSetting;

    /**
     *
     * @var ModeratorsManager $moderators
     * @inject
     */
    public $moderatorsManager;
    
    /**
     *
     * @var GridFilter $gf
     * @inject
     */
    public $gf;

    /**
     *
     * @param ForumsManager $manager
     */
    public function __construct(ForumsManager $manager)
    {
        parent::__construct($manager);
    }
    
    /**
     * 
     */
    public function __destruct()
    {
        $this->categoriesManager = null;
        $this->forumsManager     = null;
        $this->topicsManager     = null;
        $this->forumSettings     = null;
        $this->topicSetting      = null;
        $this->moderatorsManager = null;
        $this->gf                = null;
        
        parent::__destruct();
    }

    /**
     * renders topics
     *
     * @param int $category_id
     * @param int $forum_id
     * @param int $page
     * @param string|null $q
     */
    public function renderDefault($category_id, $forum_id, $page = 1)
    {
        $category   = $this->checkCategoryParam($category_id);
        $forum      = $this->checkForumParam($forum_id, $category_id);
        $forumScope = $this->loadForum($forum);        
        
        if (!$this->isAllowed($forumScope, \App\Authorization\Scopes\Forum::ACTION_VIEW)) {
            $this->error('Not allowed.', IResponse::S403_FORBIDDEN);
        }

        $forumSettings = $this->forumSettings->get();        
        $topics        = $this->topicsManager->getFluentJoinedUsersJoinedLastPostByForum($forum_id);
        
        if (isset($this['gridFilter'])) {
            $this->getComponent('gridFilter');
        }
             
        \Netpromotion\Profiler\Profiler::start('aply where');
        $this->gf->applyWhere($topics);
        \Netpromotion\Profiler\Profiler::finish('aply where');
        
        \Netpromotion\Profiler\Profiler::start('aply order by');
        $this->gf->applyOrderBy($topics);
        \Netpromotion\Profiler\Profiler::finish('aply order by');
        
        \Netpromotion\Profiler\Profiler::start('Pagination');
        $paginator = new PaginatorControl($topics, $forumSettings['pagination']['itemsPerPage'], $forumSettings['pagination']['itemsAroundPagination'], $page);

        $this->addComponent($paginator, 'paginator');

        if (!$paginator->getCount()) {
            $this->flashMessage('No topics.', self::FLASH_MESSAGE_DANGER);
        }      
        \Netpromotion\Profiler\Profiler::finish('Pagination');
        
        $moderators = $this->moderatorsManager->getAllJoinedByRight($forum_id);
        
        if (!$moderators) {
            $this->flashMessage('No moderators in forum.', self::FLASH_MESSAGE_INFO);
        }

        \Netpromotion\Profiler\Profiler::start('send to template');
        $this->template->logViews    = $this->topicSetting->get()['logViews'];
        $this->template->forum       = $forum;
        $this->template->topics      = $topics->fetchAll();
        $this->template->subForums   = $this->getManager()->getByParent($forum_id);
        $this->template->moderators  = $moderators;
        $this->template->canAddTopic = $this->isAllowed($forumScope, \App\Authorization\Scopes\Forum::ACTION_TOPIC_ADD);
        $this->template->canDeleteTopic = $this->isAllowed($forumScope, \App\Authorization\Scopes\Forum::ACTION_TOPIC_DELETE);
        \Netpromotion\Profiler\Profiler::finish('send to template');
    }

    /**
     * renders rules of forum
     *
     * @param int $category_id
     * @param int $forum_id
     */
    public function renderRules($category_id, $forum_id)
    {
        $category = $this->checkCategoryParam($category_id);
        $forum    = $this->checkForumParam($forum_id, $category_id);

        if (!$forum->getForum_rules()) {
            $this->flashMessage('No forum rules.', self::FLASH_MESSAGE_WARNING);
        }

        $this->template->forum = $forum;
    }

    protected function createComponentGridFilter()
    {
        $this->gf->setTranslator($this->getForumTranslator());
        
        $this->gf->addFilter('topic_id', 'topic_id', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('topic_name', 'topic_name', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('user_name', 'topic_author', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('topic_post_count', 'topic_post_count', GridFilter::FROM_TO_INT);
        $this->gf->addFilter('topic_view_count', 'topic_count_views', GridFilter::FROM_TO_INT);
        $this->gf->addFilter('post_add_time', 'topic_last_post_time', GridFilter::DATE_TIME);
        $this->gf->addFilter('edit', null, GridFilter::NOTHING);

        return $this->gf;
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbAll()
    {
        $breadCrumb = array_merge(
            [['link' => 'Index:default', 'text' => 'menu_index']],
            $this->categoriesManager->getBreadCrumb($this->getParameter('category_id')),
            $this->getManager()->getBreadCrumb($this->getParameter('forum_id'))
        );

        return new BreadCrumbControl($breadCrumb, $this->getForumTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbRules()
    {
        $breadCrumb = array_merge(
            [['link' => 'Index:default', 'text' => 'menu_index']],
            $this->categoriesManager->getBreadCrumb($this->getParameter('category_id')),
            $this->getManager()->getBreadCrumb($this->getParameter('forum_id')),
            [['link' => 'Forum:rules', 'text' => 'forum_rules', 'params' => [$this->getParameter('category_id'), $this->getParameter('forum_id')]]]
        );

        return new BreadCrumbControl($breadCrumb, $this->getForumTranslator());
    }
}
