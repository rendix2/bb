<?php declare(strict_types=1);

namespace App\UI\Admin\Forum\Delete;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\Model\Service\AdminException;
use App\Model\Service\ForumAdminService;
use App\UI\Admin\AdminBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use Nette\Application\UI\Form;
use Nette\Localization\Translator;

/**
 * Deleting a forum: a confirmation page with a POST form (CSRF); only an empty forum (no topics, no subforums) (ForumAdminService).
 */
class DeletePresenter extends AdminBasePresenter
{
    private ForumEntity $forum;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ForumAdminService $forumAdmin,
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
        $form->addSubmit('delete', 'admin-forum-delete.confirm')
            ->setHtmlAttribute('class', 'btn btn-danger');

        $form->onSuccess[] = function (Form $form) : void {
            $name = $this->forum->name;

            try {
                $this->forumAdmin->deleteForum($this->forum);
            } catch (AdminException $exception) {
                $form->addError($exception->getMessage());

                return;
            }

            $this->adminFlash($this->translator->translate('admin-forum-delete.success', ['name' => $name]));
            $this->redirect(':Admin:Forum:List:default');
        };

        return $form;
    }
}
