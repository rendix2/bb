<?php declare(strict_types=1);

namespace App\UI\Admin\Avatar\Delete;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\UserEntity;
use App\Model\Service\UserAvatarService;
use App\UI\Admin\AdminBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use Nette\Application\UI\Form;
use Nette\Localization\Translator;

/**
 * Deleting the avatar of a user (the image and its thumbnails): a confirmation page with a POST form (CSRF).
 */
class DeletePresenter extends AdminBasePresenter
{
    private UserEntity $avatarUser;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly UserAvatarService $avatarService,
        private readonly MenuComponentFactory $menuFactory,
        private readonly Translator $translator,
    ) {
        parent::__construct();
    }

    public function actionDefault(int $id) : void
    {
        $user = $this->em->find(UserEntity::class, $id);

        if ($user === null || $user->avatar === null) {
            $this->error('Avatar was not found.');
        }

        $this->avatarUser = $user;
    }

    public function renderDefault() : void
    {
        $this->template->avatarUser = $this->avatarUser;
    }

    protected function createComponentMenu() : MenuComponent
    {
        return $this->menuFactory->create('admin');
    }

    protected function createComponentDeleteForm() : BootstrapForm
    {
        $form = new BootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);
        $form->setTranslator($this->translator);
        $form->addProtection('admin-forum.protection');
        $form->addSubmit('delete', 'admin-avatar-delete.confirm')
            ->setHtmlAttribute('class', 'btn btn-danger');

        $form->onSuccess[] = function (Form $form) : void {
            $this->avatarService->delete($this->avatarUser);
            $this->adminFlash($this->translator->translate('admin-avatar-delete.success', ['name' => $this->avatarUser->username]));
            $this->redirect(':Admin:Avatar:List:default');
        };

        return $form;
    }
}
