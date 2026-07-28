<?php

namespace App\UI\Forum\Forum;

use App\Authorization\Scopes\ForumScope;
use App\Controls\BreadCrumbControl;
use App\Controls\GridFilter;
use App\Controls\PaginatorControl;
use App\Database\EntityManagerDecorator;
use App\ForumModule\Presenters\Base\ForumPresenter as BaseForumPresenter;
use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Repository\ForumRepository;
use App\Models\CategoryManager;
use App\Models\ForumManager;
use App\Models\ModeratorManager;
use App\services\ScopeService;
use App\Settings\ForumSettings;
use App\Settings\TopicsSetting;
use Nette\DI\Attributes\Inject;

/**
 * Description of ForumPresenter
 *
 * @author rendix2
 * @method ForumManager getManager()
 * @package App\ForumModule\Presenters
 */
final class ForumPresenter extends BaseForumPresenter
{
    #[Inject]
    public ForumSettings $forumSettings;

    #[Inject]
    public TopicsSetting $topicSetting;

    #[Inject]
    public ModeratorManager $moderatorsManager;

    #[Inject]
    public CategoryManager $categoryManager;
    
    #[Inject]
    public GridFilter $gf;

    /**
     * ForumPresenter constructor.
     *
     * @param EntityManagerDecorator $em
     * @param ForumManager $manager
     */
    public function __construct(
        private readonly EntityManagerDecorator $em,

        private readonly ScopeService $scopeService,

        private readonly ForumRepository $forumRepository,

        ForumManager $manager
    )
    {
        parent::__construct($manager);
    }

    /**
     * action default
     *
     * @param int $category_id
     * @param int $forum_id
     * @param int $page
     */
    public function actionDefault(int $category_id, int $forum_id, int $page = 1): void
    {
        $categoryEntity = $this->em
            ->getRepository(CategoryEntity::class)
            ->findOneBy(
                [
                    'id' => $category_id
                ]
            );

        if ($categoryEntity === null) {
            $this->error('Category was not found.');
        }

        if ($categoryEntity->active === false) {
            $this->error('Category is not active.');
        }

        $forum      = $this->checkForumParam($forum_id, $category_id);
        
        $forumScope = $this->scopeService->loadForum($forum);
        
        $this->requireAccess($forumScope, ForumScope::ACTION_VIEW);

        $forumSettings = $this->forumSettings->get();
        $topics        = $this->topicsManager->getFluentByForumJoinedUsersJoinedLastPost($forum_id);
        
        if (isset($this['gridFilter'])) {
            $this->getComponent('gridFilter');
        }

        $this->gf->applyWhere($topics);
        $this->gf->applyOrderBy($topics);

        $paginator = new PaginatorControl(
            $topics,
            $forumSettings['pagination']['itemsPerPage'],
            $forumSettings['pagination']['itemsAroundPagination'],
            $page
        );

        $this->addComponent($paginator, 'paginator');

        if (!$paginator->getCount()) {
            $this->flashMessage('No topics.', self::FLASH_MESSAGE_DANGER);
        }

        $this->template->canAddTopic    = $this->isAllowed($forumScope, ForumScope::ACTION_TOPIC_ADD);
        $this->template->canDeleteTopic = $this->isAllowed($forumScope, ForumScope::ACTION_TOPIC_DELETE);
        
        $this->template->forum  = $forum;
        $this->template->topics = $topics->fetchAll();
    }

    /**
     * renders topics
     *
     * @param int $category_id
     * @param int $forum_id
     * @param int $page
     */
    public function renderDefault(int $category_id, int $forum_id, int $page = 1): void
    {
        $moderators = $this->moderatorsManager->getAllByRightJoined($forum_id);
        
        if ($moderators === []) {
            $this->flashMessage('No moderators in forum.', self::FLASH_MESSAGE_INFO);
        }

        $this->getTemplate()->moderators  = $moderators;
        $this->getTemplate()->subForums   = $this->forumRepository->findByParentId($forum_id);
        $this->getTemplate()->logViews    = $this->topicSetting->get()['logViews'];
    }

    /**
     * renders rules of forum
     *
     * @param int $category_id
     * @param int $forum_id
     */
    public function renderRules(int $category_id, int $forum_id): void
    {
        $categoryEntity = $this->em
            ->getRepository(CategoryEntity::class)
            ->findOneBy(
                [
                    'id' => $category_id
                ]
            );

        if ($categoryEntity === null) {
            $this->error('Category was not found.');
        }

        if ($categoryEntity->active === false) {
            $this->error('Category is not active.');
        }

        $forum    = $this->checkForumParam($forum_id, $category_id);

        if (!$forum->getForum_rules()) {
            $this->flashMessage('No forum rules.', self::FLASH_MESSAGE_WARNING);
        }

        $this->template->forum = $forum;
    }

    /**
     *
     * @return GridFilter
     */
    protected function createComponentGridFilter(): GridFilter
    {
        $this->gf->setTranslator($this->getTranslator());
        
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
    protected function createComponentBreadCrumbAll(): BreadCrumbControl
    {
        $breadCrumb = array_merge(
            [['link' => 'Index:default', 'text' => 'menu_index']],
            $this->categoryManager->getBreadCrumb($this->getParameter('category_id')),
            $this->getManager()->getBreadCrumb($this->getParameter('forum_id'))
        );

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbRules(): BreadCrumbControl
    {
        $breadCrumb = array_merge(
            [['link' => 'Index:default', 'text' => 'menu_index']],
            $this->categoryManager->getBreadCrumb($this->getParameter('category_id')),
            $this->getManager()->getBreadCrumb($this->getParameter('forum_id')),
            [['link' => 'Forum:rules', 'text' => 'forum_rules', 'params' => [$this->getParameter('category_id'), $this->getParameter('forum_id')]]]
        );

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }
}
