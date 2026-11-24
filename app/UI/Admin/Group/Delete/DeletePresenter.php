<?php declare(strict_types=1);

namespace App\UI\Admin\Group\Delete;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\GroupEntity;
use App\Model\Service\AdminException;
use App\Model\Service\ForumAccessAdminService;
use App\UI\Admin\AdminBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use Nette\Application\UI\Form;
use Nette\Localization\Translator;

/**
 * Deleting a group: a confirmation page with a POST form (CSRF); its memberships, grants and moderation go with it, the users stay.
 */
class DeletePresenter extends AdminBasePresenter
{
    private GroupEntity $group;

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
        $this->group = $this->em->find(GroupEntity::class, $id) ?? $this->error('Group was not found.');
    }

    public function renderDefault() : void
    {
        $this->template->group = $this->group;
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
        $form->addSubmit('delete', 'admin-group-delete.confirm')
            ->setHtmlAttribute('class', 'btn btn-danger');

        $form->onSuccess[] = function (Form $form) : void {
            $name = $this->group->name;

            try {
                $this->access->deleteGroup($this->group);
            } catch (AdminException $exception) {
                $form->addError($exception->getMessage());

                return;
            }

            $this->adminFlash($this->translator->translate('admin-group-delete.success', ['name' => $name]));
            $this->redirect(':Admin:Group:List:default');
        };

        return $form;
    }
}
