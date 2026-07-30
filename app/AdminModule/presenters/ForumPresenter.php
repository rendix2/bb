<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Controls\BreadCrumbControl;
use App\Controls\GridFilter;
use App\Controls\PaginatorControl;
use App\Database\EntityManagerDecorator;
use App\Model\Repository\CategoryRepository;
use App\Model\Repository\ForumRepository;
use App\Model\Repository\PostRepository;
use App\Model\Repository\TopicRepository;
use App\Model\Repository\UserRepository;
use App\Models\ForumFacade;
use App\Models\ForumManager;
use App\Models\ModeratorManager;
use App\Models\PostManager;
use App\Models\TopicManager;
use App\Models\UsersManager;
use App\Presenters\Base\AuthenticatedPresenter;
use Doctrine\DBAL\Exception as DbalException;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;
use Tracy\ILogger;

/**
 * Description of ForumPresenter
 *
 * @author rendix2
 * @method ForumManager getManager()
 * @package App\AdminModule\Presenters
 */
class ForumPresenter extends AdminPresenter
{

    /**
     *
     * @var TopicManager $topicsManager
     * @inject
     */
    public TopicManager $topicsManager;

    /**
     *
     * @var PostManager $postsManager
     * @inject
     */
    public PostManager $postsManager;

    /**
     * @var ModeratorManager $moderatorsManager
     * @inject
     */
    public ModeratorManager $moderatorsManager;

    /**
     *
     * @var UsersManager $usersManager
     * @inject
     */
    public UsersManager $usersManager;

    /**
     *
     * @var ForumFacade $forumFacade
     * @inject
     */
    public ForumFacade $forumFacade;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly CategoryRepository     $categoryRepository,
        private readonly ForumRepository        $forumRepository,
        private readonly TopicRepository        $topicRepository,
        private readonly PostRepository         $postRepository,
        private readonly UserRepository         $userRepository,
    )
    {
        parent::__construct();
    }

    public function checkRequirements(\ReflectionClass|\ReflectionMethod $element): void
    {
        $user = $this->getUser();

        $user->getStorage()->setNamespace(self::BACK_END_NAMESPACE);

        parent::checkRequirements($element);

        if ($this->getName() !== 'Login' && !$user->isLoggedIn()) {
            $this->redirect(':Admin:Login:default');
        }

        if (!$user->isInRole('admin')) {
            $this->error('You are not admin.');
        }
    }

    public function startup()
    {
        parent::startup();

        $this->adminTranslator = $this->translatorFactory->getAdminTranslator();
    }

    /**
     * AdminPresenter beforeRender.
     */
    public function beforeRender(): void
    {
        parent::beforeRender();

        $this->getTemplate()->setTranslator($this->adminTranslator);
    }

    public function getTranslator(): ITranslator
    {
        return $this->adminTranslator;
    }

    /**
     *
     */
    public function handleReorder()
    {
        // todo
    }

    public function actionDefault(int $page = 1): void
    {
        $items = $this->getManager()->getAllFluent();

        $this->gf->applyWhere($items);
        $this->gf->applyOrderBy($items);

        $paginator = new PaginatorControl($items, 20, 5, $page);
        $this->addComponent($paginator, 'paginator');

        if (!$paginator->getCount()) {
            $this->flashMessage(sprintf('No %s.', $this->getTitle()), self::FLASH_MESSAGE_DANGER);
        }

        $this->template->items      = $items->fetchAll();
        $this->template->countItems = $paginator->getCount();
    }

    /**
     *
     * @param int $page
     */
    public function renderDefault($page = 1): void
    {
        $this->template->title = $this->getTitleOnDefault();

        $allForums = $this->forumRepository->findAll();

        $rootForums = array_filter($allForums, fn($f) => $f->getParent() === null);

        $this->getTemplate()->tree = $rootForums;
    }

    /**
     * @param int|null $id
     */
    public function renderEdit($id = null): void
    {
        if ($id) {
            if (!is_numeric($id)) {
                $this->error('Param id is not numeric.');
            }

            $forumEntity = $this->forumRepository
                ->findOneBy(
                    [
                        'id' => $id,
                    ]
                );

            if ($forumEntity === null) {
                $this->error('Item #' . $id . ' not found.');
            }

            $this['editForm']->setDefaults($forumEntity);

            $forumsByParent = $this->forumRepository->findByParentId($id);

            $subForums = $this->getManager()
                ->createForums($forumsByParent, (int)$id);

            if (!$subForums) {
                $this->flashMessage('No sub forums.', self::FLASH_MESSAGE_WARNING);
            }

            $lastTopic = $this->topicRepository->findLastByForumId($id);

            if (!$lastTopic) {
                $this->flashMessage('No last topic.', self::FLASH_MESSAGE_WARNING);
            }

            $lastPost = $this->postRepository->findLastByForumId($id);

            if ($lastPost) {
                $userData = $this->userRepository->findOneBy(
                    [
                        'id' => $lastPost->user->id
                    ]
                );
            } else {
                $userData = false;
            }

            $moderators = $this->moderatorsManager->getAllByRightJoined($id);

            $this->template->topicData = $lastTopic;
            $this->template->lastPost = $lastPost;
            $this->template->userData = $userData;
            $this->template->item = $item;
            $this->template->title = $this->getTitleOnEdit();
            $this->template->forums = $subForums;
            $this->template->moderators = $moderators;
        } else {
            $this->template->title = $this->getTitleOnAdd();
            $this->template->forums = [];
            $this->template->moderators = [];
            $this['editForm']->setDefaults([]);
        }
    }

    /**
     * @param int $id
     */
    public function actionDelete($id): void
    {
        try {
            $forumEntity = $this->forumRepository
                ->findOneBy(
                    [
                        'id' => $id,
                    ]
                );

            if ($forumEntity === null) {
                $this->error('Forum was not found');
            }

            $this->em->remove($forumEntity);
            $this->em->flush();

            $this->flashMessage('Item was deleted.', self::FLASH_MESSAGE_SUCCESS);
            $this->redrawControl('flashes');
        } catch(DbalException $exception) {
            $this->flashMessage('Item was not deleted.', self::FLASH_MESSAGE_DANGER);
        }

        $this->redirect(':' . $this->getName() . ':default');
    }

    protected function createComponentEditForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();

        $this->forumRepository->findPairs();

        $form->addGroup('forum');

        $form->addText('forum_name', 'Forum name:')
            ->setRequired(true);

        $form->addSelect('forum_parent_id', 'Parent', $this->forumRepository->findPairs())
            ->setPrompt('-')
            ->setTranslator(null);

        $form->addText('forum_description', 'Forum description:')
            ->setRequired(true);

        $form->addSelect('forum_category_id', 'Category', $this->categoryRepository->findPairs())
            ->setPrompt('-')
            ->setRequired(true)
            ->setTranslator(null);

        $form->addTextArea('forum_rules', 'Forum rules:');
        $form->addCheckbox('forum_active', 'Forum active:');

        $form->addGroup('user');

        $form->addCheckbox('forum_thank', 'Forum thank:');
        $form->addCheckbox('forum_fast_reply', 'Forum enable fast reply:');
        $form->addCheckbox('forum_post_add', 'Forum add post:');
        $form->addCheckbox('forum_post_delete', 'Forum post delete:');
        $form->addCheckbox('forum_post_update', 'Forum post update:');
        $form->addCheckbox('forum_topic_add', 'Forum topic add:');
        $form->addCheckbox('forum_topic_update', 'Forum topic update:');
        $form->addCheckbox('forum_topic_delete', 'Forum delete topic:');

        $form->addSubmit('Send', 'Send');
        $form->onValidate[] = [$this, 'editFormValidate'];
        $form->onSuccess[]  = [$this, 'editFormSuccess'];

        return $form;
    }

    /**
     * @param Form $form form
     * @param ArrayHash $values values
     */
    public function editFormSuccess(Form $form, ArrayHash $values): void
    {
        $id = $this->getParameter('id');

        try {
            if ($id) {
                $result = $this->forumFacade->update($id, $values);
            } else {
                $forumEntity = new \App\Model\Entity\ForumEntity();
                $forumEntity->name = $values->forum_name;

                $this->em->persist($forumEntity);
                $this->em->flush();

                $this->flashMessage($forumEntity->name . ' was saved.', self::FLASH_MESSAGE_SUCCESS);
                $this->redrawControl('flashes');
            }
        } catch (DbalException $exception) {
            $this->flashMessage(
                'There was some problem during saving into database. Form was NOT saved.',
                self::FLASH_MESSAGE_DANGER
            );

            Debugger::log($exception->getMessage(), ILogger::CRITICAL);
        }

        $this->redirect(':' . $this->getName() . ':default');
    }

    /**
     *
     * @return GridFilter
     */
    protected function createComponentGridFilter(): GridFilter
    {
        $this->gf->setTranslator($this->getTranslator());

        $this->gf->addFilter('multiDelete', null, GridFilter::NOTHING);
        $this->gf->addFilter('forum_id', 'forum_id', GridFilter::INT_EQUAL);
        $this->gf->addFilter('forum_name', 'forum_name', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('edit', null, GridFilter::NOTHING);
        $this->gf->addFilter('delete', null, GridFilter::NOTHING);

        return $this->gf;
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbAll(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['text' => 'menu_forums']
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbEdit(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'Forum:default', 'text' => 'menu_forums'],
            2 => ['link' => 'Forum:edit', 'text' => 'menu_forum'],
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }
}
