<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Presenters\Base\AdminPresenter;
use App\Controls\BreadCrumbControl;
use App\Controls\GridFilter;
use App\Controls\PaginatorControl;
use App\Controls\UserSearchControl;
use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\Model\Repository\CategoryRepository;
use App\Model\Repository\ForumRepository;
use App\Model\Repository\UserRepository;
use App\Models\Forums2GroupsManager;
use App\Models\ForumManager;
use App\Models\GroupManager;
use App\Models\User2GroupManager;
use Dibi\DriverException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;
use Tracy\ILogger;

/**
 * Description of GroupPresenter
 *
 * @author rendix2
 * @method GroupManager getManager()
 * @package App\AdminModule\Presenters
 */
class GroupPresenter extends AdminPresenter
{
    /**
     * @var User2GroupManager $users2Groups
     * @inject
     */
    public User2GroupManager $users2GroupsManager;

    /**
     * @var Forums2GroupsManager $forums2groups
     * @inject
     */
    public Forums2GroupsManager $forums2groupsManager;

    /**
     * @var ForumManager $forumsManager
     * @inject
     */
    public ForumManager $forumsManager;

    public function __construct(
        private readonly EntityManagerDecorator $em,

        private readonly UserSearchControl $userSearchControl,

        private readonly UserRepository $userRepository,

        private readonly CategoryRepository $categoryRepository,
        private readonly ForumRepository    $forumRepository,
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

    /**
     * AdminPresenter startup.
     */
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

    public function getTranslator(): Translator
    {
        return $this->adminTranslator;
    }

    /**
     * @param array $added_forum_row
     *
     * @return array
     */
    private function map(array $added_forum_row): array
    {
        $result = [];

        $forums = $this->forumRepository->findAll();

        foreach ($forums as $forum) {
            $result[$forum->forum_id] = false;

            foreach ($added_forum_row as $forum_row) {
                if ($forum->forum_id === (int)$forum_row) {
                    $result[$forum->forum_id] = true;
                }
            }
        }

        return $result;
    }

    /**
     *
     * @param int $user_id
     * @param string $user_name
     */
    public function handleSetUserId($user_id, $user_name)
    {
        $this['editForm']->setDefaults(
            [
                'group_moderator_id' => $user_id,
                'group_moderator' => $user_name
            ]
        );

        $this->redrawControl('editForm');
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
     * @param int $page
     */
    public function renderDefault(int $page = 1): void
    {
        $this->template->title = $this->getTitleOnDefault();
    }

    public function actionDelete(int $id)
    {
        if (!is_numeric($id)) {
            $this->error('Parameter is not numeric.');
        }

        $result = $this->getManager()->delete($id);

        if ($result) {
            $this->flashMessage('Item was deleted.', self::FLASH_MESSAGE_SUCCESS);
        } else {
            $this->flashMessage('Item was not deleted.', self::FLASH_MESSAGE_DANGER);
        }

        $this->redirect(':' . $this->getName() . ':default');
    }

    /**
     * @param int|null $id
     */
    public function renderEdit($id = null): void
    {
        if ($id) {
            if (!is_numeric($id)) {
                $this->error('Parameter $id of CrudPresenter::renderEdit($id) is not numeric.');
            }

            $item = $this->getManager()->getById($id);

            if (!$item) {
                $this->error('Item $' . $this->getTitle() . '[' . $id . '] was not found.');
            }

            $this['editForm']->setDefaults($item);

            $this->template->item_id = $id;
            $this->template->item    = $item;
            $this->template->title   = $this->getTitleOnEdit();
        } else {
            $this->template->item_id = null;
            $this->template->title   = $this->getTitleOnAdd();
            $this->template->item    = [];

            $this['editForm']->setDefaults([]);
        }

        if ($id) {
            if ($this->getParameter('user_name') && $this->getParameter('user_id')) {
                $this['editForm']->setDefaults(
                    [
                        'group_moderator' => $this->getParameter('user_name'),
                        'group_moderator_id' => $this->getParameter('user_id')
                    ]
                );
            } else {
                $item = $this->template->item;

                $moderator = $this->userRepository->findOneBy(
                    [
                        'id' => $item->group_moderator_id,
                    ]
                );

                $this['editForm']->setDefaults(['group_moderator' => $moderator->user_name]);
            }
        }

        $permission = [];
        $groupForums = $this->forums2groupsManager->getAllByRight($id);

        foreach ($groupForums as $groupForum) {
            $permission[$groupForum->forum_id]['post_add'] = $groupForum->post_add;
            $permission[$groupForum->forum_id]['post_update'] = $groupForum->post_update;
            $permission[$groupForum->forum_id]['post_delete'] = $groupForum->post_delete;
            $permission[$groupForum->forum_id]['topic_add'] = $groupForum->topic_add;
            $permission[$groupForum->forum_id]['topic_update'] = $groupForum->topic_update;
            $permission[$groupForum->forum_id]['topic_delete'] = $groupForum->topic_delete;
            $permission[$groupForum->forum_id]['topic_thank'] = $groupForum->topic_thank;
            $permission[$groupForum->forum_id]['topic_fast_reply'] = $groupForum->topic_fast_reply;
        }

        $forums = $this->forumRepository->findAll();

        $this->getTemplate()->countOfUsers = $this->users2GroupsManager->getCountByRight($id);
        $this->getTemplate()->forums = $this->forumsManager->createForums($forums, 0);
        $this->getTemplate()->permissions = $permission;
    }

    protected function createComponentEditForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();

        $form->addHidden('group_moderator_id');
        $form->addText('group_name', 'Group name:')
            ->setRequired(true);

        $form->addText('group_moderator', 'Group moderator:')
            ->setDisabled();

        $form->addSubmit('Send', 'Send');

        $form->onValidate[] = [$this, 'editForumValidate'];
        $form->onSuccess[] = [$this, 'editFormSuccess'];

        return $form;
    }

    /**
     *
     * @return GridFilter
     */
    protected function createComponentGridFilter()
    {
        $this->gf->setTranslator($this->getTranslator());

        $this->gf->addFilter('multiDelete', null, GridFilter::NOTHING);
        $this->gf->addFilter('group_id', 'group_id', GridFilter::INT_EQUAL);
        $this->gf->addFilter('group_name', 'group_name', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('edit', null, GridFilter::NOTHING);
        $this->gf->addFilter('delete', null, GridFilter::NOTHING);

        return $this->gf;
    }

    /**
     * @param Form $form
     * @param ArrayHash $values
     */
    public function editFormSuccess(Form $form, ArrayHash $values): void
    {
        unset($values->group_moderator);

        $id = $this->getParameter('id');

        try {
            if ($id) {
                $result = $this->getManager()->update($id, $values);
            } else {
                $result = $id = $this->getManager()->add($values);
            }

            if ($result) {
                $this->flashMessage($this->getTitle() . ' was saved.', self::FLASH_MESSAGE_SUCCESS);
            } else {
                $this->flashMessage('Nothing to save.', self::FLASH_MESSAGE_INFO);
            }
        } catch (DriverException $e) {
            $this->flashMessage(
                'There was some problem during saving into database. Form was NOT saved.',
                self::FLASH_MESSAGE_DANGER
            );

            Debugger::log($e->getMessage(), ILogger::CRITICAL);
        }

        $this->redirect(':' . $this->getName() . ':default');
    }

    /**
     *
     * @return UserSearchControl
     */
    protected function createComponentUserSearch(): UserSearchControl
    {
        return $this->userSearchControl;
    }

    protected function createComponentForumsForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();
        $form->setTranslator($this->getTranslator());

        $form->addSubmit('send', 'Send');
        $form->onSuccess[] = [$this, 'forumsSuccess'];

        return $form;
    }

    /**
     * @param Form $form
     * @param ArrayHash $values
     */
    public function forumsSuccess(Form $form, ArrayHash $values): void
    {
        $group_id = $this->getParameter('id');

        $post_add = $form->getHttpData($form::DATA_TEXT, 'post_add[]');
        $post_update = $form->getHttpData($form::DATA_TEXT, 'post_update[]');
        $post_delete = $form->getHttpData($form::DATA_TEXT, 'post_delete[]');
        $topic_add = $form->getHttpData($form::DATA_TEXT, 'topic_add[]');
        $topic_update = $form->getHttpData($form::DATA_TEXT, 'topic_update[]');
        $topic_delete = $form->getHttpData($form::DATA_TEXT, 'topic_delete[]');
        $forum_id = $form->getHttpData($form::DATA_TEXT, 'forum_id[]');
        $topic_thank = $form->getHttpData($form::DATA_TEXT, 'topic_thank[]');
        $topic_fast_reply = $form->getHttpData($form::DATA_TEXT, 'topic_fast_reply[]');

        $count = $this->em
            ->getRepository(ForumEntity::class)
            ->count();

        $forums = $this->em->getRepository(ForumEntity::class)
            ->findAll();

        $forumsPermissions = [];
        $groupPermissions = [];

        foreach ($forums as $forum) {
            $forumsPermissions[$forum->forum_id] = $forum->forum_id;
            $groupPermissions[$forum->forum_id] = (int)$group_id;
        }

        $data = [
            'post_add' => $this->map(array_pad($post_add, $count + 1, 0)),
            'post_update' => $this->map(array_pad($post_update, $count + 1, 0)),
            'post_delete' => $this->map(array_pad($post_delete, $count + 1, 0)),
            'topic_add' => $this->map(array_pad($topic_add, $count + 1, 0)),
            'topic_update' => $this->map(array_pad($topic_update, $count + 1, 0)),
            'topic_delete' => $this->map(array_pad($topic_delete, $count + 1, 0)),
            'topic_thank' => $this->map(array_pad($topic_thank, $count + 1, 0)),
            'topic_fast_reply' => $this->map(array_pad($topic_fast_reply, $count + 1, 0)),
            'forum_id' => $forumsPermissions,
            'group_id' => $groupPermissions,
        ];

        $this->forums2groupsManager->addForums2group($group_id, $data);
    }

    /**
     * @return BreadCrumbControl
     */
    protected function createComponentBreadCrumbAll(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['text' => 'menu_groups']
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
            1 => ['link' => 'Group:default', 'text' => 'menu_groups'],
            2 => ['link' => 'Group:edit', 'text' => 'menu_group'],
        ];

        return new BreadCrumbControl($breadCrumb, $this->getTranslator());
    }
}
