<?php declare(strict_types=1);

namespace App\UI\Admin\Forum\Access;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\ForumPermissions;
use App\Model\Entity\UserForumPermissionEntity;
use App\Model\Entity\UserModeratorEntity;
use App\Model\Service\AdminException;
use App\Model\Service\ForumAccessAdminService;
use App\UI\Admin\AdminBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Nette\Localization\Translator;

/**
 * The access of single users to a forum: the users who moderate it and the own grants of users (the groups have theirs on
 * Admin:Group:Edit).
 */
class AccessPresenter extends AdminBasePresenter
{
    private ForumEntity $forum;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ForumAccessAdminService $access,
        private readonly MenuComponentFactory $menuFactory,
        private readonly Translator $translator,
    ) {
        parent::__construct();
    }

    public function actionDefault(int $id) : void
    {
        $this->forum = $this->em->find(ForumEntity::class, $id) ?? $this->error('Forum was not found.');
    }

    public function renderDefault() : void
    {
        $this->template->forum = $this->forum;
        $this->template->moderators = $this->access->userModerators($this->forum);
        $this->template->grants = $this->access->userGrants($this->forum);
        $this->template->flags = ForumPermissions::FLAGS;
    }

    protected function createComponentMenu() : MenuComponent
    {
        return $this->menuFactory->create('admin');
    }

    protected function createComponentAddModeratorForm() : BootstrapForm
    {
        $form = $this->form();
        $form->addText('username', 'admin-forum-access.username')
            ->setRequired('admin-forum-access.usernameRequired');
        $form->addSubmit('add', 'admin-forum-access.addModerator');

        $form->onSuccess[] = function (Form $form) : void {
            try {
                $user = $this->access->addUserModerator($this->forum, (string) $form->getValues('array')['username']);
            } catch (AdminException $exception) {
                $form->addError($this->translator->translate($exception->getMessage(), $exception->parameters), false);

                return;
            }

            $this->adminFlash($this->translator->translate('admin-forum-access.moderatorAdded', ['username' => $user->username]));
            $this->redirect('this');
        };

        return $form;
    }

    protected function createComponentRemoveModeratorForm() : Multiplier
    {
        return new Multiplier(function (string $moderatorId) : BootstrapForm {
            $form = $this->form();
            $form->addSubmit('remove', 'admin-forum-access.remove')
                ->setHtmlAttribute('class', 'btn btn-outline-danger btn-sm');

            $form->onSuccess[] = function () use ($moderatorId) : void {
                $moderator = $this->em->find(UserModeratorEntity::class, (int) $moderatorId);

                if ($moderator === null || $moderator->forum->id !== $this->forum->id) {
                    $this->error('Moderator was not found.');
                }

                $username = $moderator->user->username;
                $this->access->removeUserModerator($moderator);
                $this->adminFlash($this->translator->translate('admin-forum-access.moderatorRemoved', ['username' => $username]));
                $this->redirect('this');
            };

            return $form;
        });
    }

    /**
     * Sets the own grant of a user (all flags unchecked deletes it).
     */
    protected function createComponentGrantForm() : BootstrapForm
    {
        $form = $this->form();
        $form->addText('username', 'admin-forum-access.username')
            ->setRequired('admin-forum-access.usernameRequired');
        $permissions = $form->addContainer('permissions');

        foreach (ForumPermissions::FLAGS as $flag) {
            $permissions->addCheckbox($flag, 'admin-access.flag.' . $flag);
        }

        $form->addSubmit('save', 'admin-forum-access.saveGrant');

        $form->onSuccess[] = function (Form $form) : void {
            /** @var array{username: string, permissions: array<string, bool>} $values */
            $values = $form->getValues('array');
            $permissions = new ForumPermissions();
            $permissions->setFlags($values['permissions']);

            try {
                $user = $this->access->setUserGrant($this->forum, $values['username'], $permissions);
            } catch (AdminException $exception) {
                $form->addError($this->translator->translate($exception->getMessage(), $exception->parameters), false);

                return;
            }

            $this->adminFlash($this->translator->translate('admin-forum-access.grantSaved', ['username' => $user->username]));
            $this->redirect('this');
        };

        return $form;
    }

    protected function createComponentRemoveGrantForm() : Multiplier
    {
        return new Multiplier(function (string $grantId) : BootstrapForm {
            $form = $this->form();
            $form->addSubmit('remove', 'admin-forum-access.remove')
                ->setHtmlAttribute('class', 'btn btn-outline-danger btn-sm');

            $form->onSuccess[] = function () use ($grantId) : void {
                $grant = $this->em->find(UserForumPermissionEntity::class, (int) $grantId);

                if ($grant === null || $grant->forum->id !== $this->forum->id) {
                    $this->error('Grant was not found.');
                }

                $username = $grant->user->username;
                $this->access->setUserGrant($this->forum, $username, new ForumPermissions());
                $this->adminFlash($this->translator->translate('admin-forum-access.grantRemoved', ['username' => $username]));
                $this->redirect('this');
            };

            return $form;
        });
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
