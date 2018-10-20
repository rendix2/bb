<?php

namespace App\ForumModule\Presenters;

use App\Controls\BreadCrumbControl;
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
    public $moderators;
    
    /**
     *
     * @var ForumSettings $forumsSettings
     * @inject
     */
    public $forumSettings;

    /**
     *
     * @param ForumsManager $manager
     */
    public function __construct(ForumsManager $manager)
    {
        parent::__construct($manager);
    }

    /**
     * renders topics
     *
     * @param int $category_id
     * @param int $forum_id
     * @param int $page
     * @param string|null $q
     */
    public function renderDefault($category_id, $forum_id, $page = 1, $q = null)
    {
        if (!$this->getUser()->isAllowed($forum_id, 'forum_view')) {
            $this->error('Not allowed.', IResponse::S403_FORBIDDEN);
        }

        $category      = $this->checkCategoryParam($category_id);
        $forum         = $this->checkForumParam($forum_id, $category_id);
        $forumSettings = $this->forumSettings->get();
        
        $topics    = $this->topicsManager->getFluentJoinedUsersJoinedLastPostByForum($forum_id);
        $paginator = new PaginatorControl($topics, $forumSettings['pagination']['itemsPerPage'], $forumSettings['pagination']['itemsAroundPagination'], $page);

        $this->addComponent($paginator, 'paginator');

        if (!$paginator->getCount()) {
            $this->flashMessage('No topics.', self::FLASH_MESSAGE_DANGER);
        }
        
        if ($q) {
            //$topics = $topics->where('topic_id IN ( SELECT post_topic_id FROM posts WHERE MATCH(post_title,
            // post_text) AGAINST (%s IN BOOLEAN MODE) AND post_forum_id = %i) OR MATCH(topic_name) AGAINST (%s IN BOOLEAN MODE)', $q, $forum_id, $q);

            $topics = $this->topicsManager->findTopic($topics, $q, $forum_id);
            $this['searchInForumForm']->setDefaults(['search_form' => $q]);
        }
        
        $moderators = $this->moderators->getAllJoinedByRight($forum_id);
        
        if (!$moderators) {
            $this->flashMessage('No moderators in forum.', self::FLASH_MESSAGE_INFO);
        }

        $this->template->logViews    = $this->topicSetting->canLogView();
        $this->template->forum       = $forum;
        $this->template->topics      = $topics->fetchAll();
        $this->template->subForums   = $this->getManager()->getByParent($forum_id);
        $this->template->moderators  = $moderators;
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

        if (!$forum->forum_rules) {
            $this->flashMessage('No forum rules.', self::FLASH_MESSAGE_WARNING);
        }

        $this->template->forum = $forum;
    }

    /**
     * @param int $forum_id
     */
    public function renderSearchForum($forum_id)
    {
    }

    /**
     * @return ForumSearchInForumForm
     */
    protected function createComponentSearchInForumForm()
    {
        return new ForumSearchInForumForm($this->getForumTranslator());
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
