<?php

namespace App\UI\Forum\Forum;

use App\Authorization\Scopes\ForumScope;
use App\Controls\BreadCrumbControl;
use App\Database\EntityManagerDecorator;
use App\Model\Entity\TopicEntity;
use App\Model\Repository\CategoryRepository;
use App\Model\Repository\ForumRepository;
use App\services\BreadcrumbService;
use App\services\ScopeService;
use App\Settings\ForumSettings;
use App\Settings\TopicsSetting;
use Contributte\Datagrid\Datagrid;
use Nette\Application\UI\Presenter;
use Nette\DI\Attributes\Inject;

/**
 * Description of ForumPresenter
 *
 * @author rendix2
 * @package App\ForumModule\Presenters
 */
final class ForumPresenter extends Presenter
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
    )
    {
        parent::__construct();
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
        $forumEntity = $this->forumRepository->findOneBy(
            [
                'id' => $forum_id,
            ]
        );

        $subForums = $this->forumRepository->findByParentId($forum_id);

        $this->getTemplate()->moderators  = $forumEntity->moderatorUsers;
        $this->getTemplate()->subForums   = $subForums;
        $this->getTemplate()->logViews    = $this->topicSetting->get()['logViews'];
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
}
