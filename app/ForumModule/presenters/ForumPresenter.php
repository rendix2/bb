<?php

namespace App\UI\Forum\Forum;

use App\Authorization\Scopes\ForumScope;
use App\Controls\BreadCrumbControl;
use App\Controls\GridFilter;
use App\Database\EntityManagerDecorator;
use App\ForumModule\Presenters\Base\ForumPresenter as BaseForumPresenter;
use App\Model\Entity\TopicEntity;
use App\Model\Repository\CategoryRepository;
use App\Model\Repository\ForumRepository;
use App\Models\ForumManager;
use App\services\BreadcrumbService;
use App\services\ScopeService;
use App\Settings\ForumSettings;
use App\Settings\TopicsSetting;
use Contributte\Datagrid\Datagrid;
use Nette\DI\Attributes\Inject;

/**
 * Description of ForumPresenter
 *
 * @author rendix2
 * @package App\ForumModule\Presenters
 */
final class ForumPresenter extends BaseForumPresenter
{
    #[Inject]
    public ForumSettings $forumSettings;

    #[Inject]
    public TopicsSetting $topicSetting;

    public function __construct(
        private readonly EntityManagerDecorator $em,

        private readonly ScopeService $scopeService,

        private readonly CategoryRepository $categoryRepository,
        private readonly ForumRepository    $forumRepository,

        private readonly BreadcrumbService $breadcrumbService,

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
        $categoryEntity = $this->categoryRepository
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

        $forumEntity = $this->forumRepository
            ->findOneBy(
                [
                    'id' => $forum_id,
                ]
            );
        
        $forumScope = $this->scopeService->loadForum($forumEntity);
        
        $this->requireAccess($forumScope, ForumScope::ACTION_VIEW);

        $this->template->canAddTopic    = $this->isAllowed($forumScope, ForumScope::ACTION_TOPIC_ADD);
        $this->template->canDeleteTopic = $this->isAllowed($forumScope, ForumScope::ACTION_TOPIC_DELETE);
        
        $this->template->forum  = $forumEntity;
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
        //$moderators = $this->moderatorsManager->getAllByRightJoined($forum_id);

        /*
        if ($moderators === []) {
            $this->flashMessage('No moderators in forum.', self::FLASH_MESSAGE_INFO);
        }
        */

        //$this->getTemplate()->moderators  = $moderators;
        $this->getTemplate()->moderators  = [];
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
        $categoryEntity = $this->categoryRepository
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

        $forumEntity = $this->forumRepository
            ->findOneBy(
                [
                    'id' => $forum_id,
                ]
            );

        if ($forumEntity->rules === null) {
            $this->flashMessage('No forum rules.', self::FLASH_MESSAGE_WARNING);
        }

        $this->template->forum = $forumEntity;
    }

    protected function createComponentDataGrid(): Datagrid
    {
        $dataSource = $this->em
            ->getRepository(TopicEntity::class)

            ->createQueryBuilder('_t')

            ->addSelect('_u')
            ->innerJoin('_t.user', '_u')

            ->addSelect('_lp')
            ->leftJoin('_t.lastPost', '_lp');

        $grid = new Datagrid();

        $grid->setPrimaryKey('uuid');
        $grid->setDataSource($dataSource);

        $grid->addColumnText('name', 'Name');

        $grid->addColumnText('user', 'Username')
            ->setRenderer(
                function(TopicEntity $topicEntity) : string {
                    return $topicEntity->user->username;
                }
            );

        $grid->addColumnNumber('post_count', 'Post count')
            ->setRenderer(
                function(TopicEntity $topicEntity) : string {
                    return $topicEntity->posts->count();
                }
            );

        $grid->addColumnNumber('view count', 'View count')
            ->setRenderer(
                function(TopicEntity $topicEntity) : string {
                    return $topicEntity->posts->count();
                }
            );

        $grid->addColumnDateTime('createdAt', 'Created at');

        return $grid;
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbAll(): BreadCrumbControl
    {
        $breadCrumb = array_merge(
            [['link' => 'Index:default', 'text' => 'menu_index']],
            $this->breadcrumbService->getCategoryBreadCrumb($this->getParameter('category_id')),
            $this->breadcrumbService->getForumBreadCrumb($this->getParameter('forum_id'))
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
            $this->breadcrumbService->getCategoryBreadCrumb($this->getParameter('category_id')),
            $this->breadcrumbService->getForumBreadCrumb($this->getParameter('forum_id')),
            [['link' => 'Forum:rules', 'text' => 'forum_rules', 'params' => [$this->getParameter('category_id'), $this->getParameter('forum_id')]]]
        );

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }
}
