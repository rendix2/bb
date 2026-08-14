<?php

namespace App\AdminModule\Presenters;

use App\Controls\BreadCrumbControl;
use App\Controls\GridFilter;
use App\Controls\PaginatorControl;
use App\Controls\UserSearchControl;
use App\Database\EntityManagerDecorator;
use App\Model\Entity\GroupEntity;
use App\Model\Repository\ForumRepository;
use App\Model\Repository\GroupForumPermissionRepository;
use App\Model\Repository\GroupRepository;
use App\Model\Repository\UserRepository;
use App\Models\ForumFacade;
use App\Models\Forums2GroupsManager;
use Doctrine\DBAL\Exception as DbalException;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Localization\Translator;
use Nette\Utils\ArrayHash;

class GroupPresenter extends Presenter
{
    /**
     * @var Forums2GroupsManager $forums2groups
     * @inject
     */
    public Forums2GroupsManager $forums2groupsManager;

    public function __construct(
        private readonly EntityManagerDecorator $em,

        private readonly ForumFacade $forumFacade,

        private readonly UserSearchControl $userSearchControl,

        private readonly UserRepository $userRepository,
        private readonly GroupRepository $groupRepository,

        private readonly ForumRepository $forumRepository,

        private readonly GroupForumPermissionRepository $groupForumPermissionRepository,

        private readonly Translator $translator,
    ) {
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

    private function map(array $added_forum_row): array
    {
        $result = [];

        $forums = $this->forumRepository->findAll();

        foreach ($forums as $forum) {
            $result[$forum->forum_id] = false;

            foreach ($added_forum_row as $forum_row) {
                if ($forum->forum_id === (int) $forum_row) {
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
                'group_moderator' => $user_name,
            ],
        );

        $this->redrawControl('editForm');
    }

    public function actionDefault(int $page = 1): void
    {
        $items = $this->groupRepository->findAll();

        $this->gf->applyWhere($items);
        $this->gf->applyOrderBy($items);

        $paginator = new PaginatorControl($items, 20, 5, $page);
        $this->addComponent($paginator, 'paginator');

        if (!$paginator->getCount()) {
            $this->flashMessage(sprintf('No %s.', $this->getTitle()), self::FLASH_MESSAGE_DANGER);
        }

        $this->template->items = $items->fetchAll();
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

        $groupEntity = $this->groupRepository
            ->findOneBy(
                [
                    'id' => $id,
                ],
            );

        if ($groupEntity === null) {
            $this->error('Group not found');
        }

        try {
            $this->em->remove($groupEntity);
            $this->em->flush();
            $this->flashMessage('Item was deleted.', self::FLASH_MESSAGE_SUCCESS);
        } catch (DbalException $exception) {
            $this->flashMessage('Item was not deleted.', self::FLASH_MESSAGE_DANGER);
            $this->redrawControl('flashes');
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

            $groupEntity = $this->groupRepository
                ->findOneBy(
                    [
                        'id' => $id,
                    ],
                );

            if ($groupEntity === null) {
                $this->error('Item $' . $this->getTitle() . '[' . $id . '] was not found.');
            }

            $this['editForm']->setDefaults($groupEntity);

            $this->template->item_id = $id;
            $this->template->item = $item;
            $this->template->title = $this->getTitleOnEdit();
        } else {
            $this->template->item_id = null;
            $this->template->title = $this->getTitleOnAdd();
            $this->template->item = [];

            $this['editForm']->setDefaults([]);
        }

        if ($id) {
            if ($this->getParameter('user_name') && $this->getParameter('user_id')) {
                $this['editForm']->setDefaults(
                    [
                        'group_moderator' => $this->getParameter('user_name'),
                        'group_moderator_id' => $this->getParameter('user_id'),
                    ],
                );
            } else {
                $item = $this->template->item;

                $moderator = $this->userRepository->findOneBy(
                    [
                        'id' => $item->group_moderator_id,
                    ],
                );

                $this['editForm']->setDefaults(['group_moderator' => $moderator->user_name]);
            }
        }

        $permission = [];
        $groupForums = $this->groupForumPermissionRepository
            ->findBy(
                [
                    'group' => $id,
                ]
            );

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

        $countOfUsers = $this->groupForumPermissionRepository
            ->count(
                [
                    'group' => $id,
                ]
            );

        $forums = $this->forumRepository->findAll();

        $this->getTemplate()->countOfUsers = $countOfUsers;
        $this->getTemplate()->forums = $this->forumFacade->createForums($forums, 0);
        $this->getTemplate()->permissions = $permission;
    }

    protected function createComponentEditForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();

        $form->addHidden('moderator_id');
        $form->addText('group_name', 'Group name:')
            ->setRequired(true);

        $form->addText('group_moderator', 'Group moderator:')
            ->setDisabled();

        $form->addSubmit('Send', 'Send');

        $form->onValidate[] = [$this, 'editForumValidate'];
        $form->onSuccess[] = [$this, 'editFormSuccess'];

        return $form;
    }

    protected function createComponentGridFilter()
    {
        $this->gf->setTranslator($this->translator);

        $this->gf->addFilter('multiDelete', null, GridFilter::NOTHING);
        $this->gf->addFilter('group_id', 'group_id', GridFilter::INT_EQUAL);
        $this->gf->addFilter('name', 'group_name', GridFilter::TEXT_LIKE);
        $this->gf->addFilter('edit', null, GridFilter::NOTHING);
        $this->gf->addFilter('delete', null, GridFilter::NOTHING);

        return $this->gf;
    }

    public function editFormSuccess(Form $form, ArrayHash $values): void
    {
        unset($values->group_moderator);

        $id = $this->getParameter('id');

        $groupModeratorUserEntity = $this->userRepository
            ->findOneBy(
                [
                    'id' => $values->moderator_id,
                ],
            );

        if ($id === null) {
            $groupEntity = new GroupEntity();
        } else {
            $groupEntity = $this->groupRepository
                ->findOneBy(
                    [
                        'id' => $id,
                    ],
                );
        }

        $groupEntity->name = $values->name;
        $groupEntity->moderator = $groupModeratorUserEntity;

        try {
            $this->flashMessage($this->getTitle() . ' was saved.', 'success');
        } catch (DbalException $exception) {
            $this->flashMessage(
                'There was some problem during saving into database. Form was NOT saved.',
                'danger',
            );
        } finally {
            $this->redrawControl('flashes');
        }

        $this->redirect(':' . $this->getName() . ':default');
    }

    protected function createComponentUserSearch(): UserSearchControl
    {
        return $this->userSearchControl;
    }

    protected function createComponentForumsForm(): \Contributte\FormsBootstrap\BootstrapForm
    {
        $form = new \Contributte\FormsBootstrap\BootstrapForm();
        $form->setTranslator($this->translator);

        $form->addSubmit('send', 'Send');
        $form->onSuccess[] = [$this, 'forumsSuccess'];

        return $form;
    }

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

        $count = $this->forumRepository
            ->count();

        $forums = $this->forumRepository
            ->findAll();

        $forumsPermissions = [];
        $groupPermissions = [];

        foreach ($forums as $forum) {
            $forumsPermissions[$forum->forum_id] = $forum->forum_id;
            $groupPermissions[$forum->forum_id] = (int) $group_id;
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

    protected function createComponentBreadCrumbAll(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['text' => 'menu_groups'],
        ];

        return new BreadCrumbControl($breadCrumb, $this->translator);
    }

    protected function createComponentBreadCrumbEdit(): BreadCrumbControl
    {
        $breadCrumb = [
            0 => ['link' => 'Index:default', 'text' => 'menu_index'],
            1 => ['link' => 'Group:default', 'text' => 'menu_groups'],
            2 => ['link' => 'Group:edit', 'text' => 'menu_group'],
        ];

        return new BreadCrumbControl($breadCrumb, $this->translator);
    }
}
