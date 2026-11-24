<?php declare(strict_types=1);

namespace App\UI\Admin\Group\Edit;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\ForumPermissions;
use App\Model\Entity\GroupEntity;
use App\Model\Entity\UserEntity;
use App\Model\Service\AdminException;
use App\Model\Service\ForumAccessAdminService;
use App\UI\Admin\Accessory\GroupFormFactory;
use App\UI\Admin\AdminBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Nette\Localization\Translator;

/**
 * A group: its name, members and its access to every forum (the flags it adds to the logged-in members and whether it
 * moderates the forum).
 */
class EditPresenter extends AdminBasePresenter
{
    private GroupEntity $group;

    /** @var list<ForumEntity> */
    private array $forums;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ForumAccessAdminService $access,
        private readonly GroupFormFactory $groupFormFactory,
        private readonly MenuComponentFactory $menuFactory,
        private readonly Translator $translator,
    ) {
        parent::__construct();
    }

    public function actionDefault(int $id) : void
    {
        $this->group = $this->em->find(GroupEntity::class, $id) ?? $this->error('Group was not found.');
        $this->forums = $this->em->getRepository(ForumEntity::class)->findBy([], ['sortOrder' => 'ASC', 'id' => 'ASC']);
    }

    public function renderDefault() : void
    {
        $this->template->group = $this->group;
        $this->template->members = $this->access->members($this->group);
        $this->template->forums = $this->forums;
        $this->template->flags = ForumPermissions::FLAGS;
    }

    protected function createComponentMenu() : MenuComponent
    {
        return $this->menuFactory->create('admin');
    }

    protected function createComponentGroupForm() : BootstrapForm
    {
        return $this->groupFormFactory->create($this->group, function (GroupEntity $group) : void {
            $this->adminFlash($this->translator->translate('admin-group-edit.saved', ['name' => $group->name]));
            $this->redirect('this');
        });
    }

    protected function createComponentAddMemberForm() : BootstrapForm
    {
        $form = $this->form();
        $form->addText('username', 'admin-group-edit.member.username')
            ->setRequired('admin-group-edit.member.required');
        $form->addSubmit('add', 'admin-group-edit.member.add');

        $form->onSuccess[] = function (Form $form) : void {
            try {
                $user = $this->access->addMember($this->group, (string) $form->getValues('array')['username']);
            } catch (AdminException $exception) {
                $form->addError($this->translator->translate($exception->getMessage(), $exception->parameters), false);

                return;
            }

            $this->adminFlash($this->translator->translate('admin-group-edit.member.added', ['username' => $user->username]));
            $this->redirect('this');
        };

        return $form;
    }

    /**
     * A button per member (keyed by the id of the user, a key cannot contain a dash).
     */
    protected function createComponentRemoveMemberForm() : Multiplier
    {
        return new Multiplier(function (string $userId) : BootstrapForm {
            $form = $this->form();
            $form->addSubmit('remove', 'admin-group-edit.member.remove')
                ->setHtmlAttribute('class', 'btn btn-outline-danger btn-sm');

            $form->onSuccess[] = function () use ($userId) : void {
                $user = $this->em->find(UserEntity::class, (int) $userId) ?? $this->error('User was not found.');
                $this->access->removeMember($this->group, $user);
                $this->adminFlash($this->translator->translate('admin-group-edit.member.removed', ['username' => $user->username]));
                $this->redirect('this');
            };

            return $form;
        });
    }

    /**
     * One form for all forums: a checkbox "a<forum id>_<flag>" per flag and "a<forum id>_moderator".
     */
    protected function createComponentAccessForm() : BootstrapForm
    {
        $form = $this->form();
        $access = $this->access->groupAccess($this->group);

        foreach ($this->forums as $forum) {
            $current = $access[$forum->id] ?? ['permissions' => new ForumPermissions(), 'moderator' => false];

            foreach (ForumPermissions::FLAGS as $flag) {
                $form->addCheckbox('a' . $forum->id . '_' . $flag)
                    ->setDefaultValue($current['permissions']->$flag);
            }

            $form->addCheckbox('a' . $forum->id . '_moderator')
                ->setDefaultValue($current['moderator']);
        }

        $form->addSubmit('save', 'admin-forum.save');

        $form->onSuccess[] = function (Form $form) : void {
            /** @var array<string, bool> $values */
            $values = $form->getValues('array');

            foreach ($this->forums as $forum) {
                $flags = [];

                foreach (ForumPermissions::FLAGS as $flag) {
                    $flags[$flag] = $values['a' . $forum->id . '_' . $flag];
                }

                $permissions = new ForumPermissions();
                $permissions->setFlags($flags);
                $this->access->setGroupAccess($this->group, $forum, $permissions, $values['a' . $forum->id . '_moderator']);
            }

            $this->adminFlash($this->translator->translate('admin-group-edit.accessSaved'));
            $this->redirect('this');
        };

        return $form;
    }

    private function form() : BootstrapForm
    {
        $form = new BootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);
        $form->setTranslator($this->translator);
        $form->addProtection('admin-forum.protection');

        return $form;
    }
}
