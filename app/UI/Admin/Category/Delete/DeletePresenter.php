<?php declare(strict_types=1);

namespace App\UI\Admin\Category\Delete;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\CategoryEntity;
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
 * Deleting a category: a confirmation page with a POST form (CSRF); only an empty category (ForumAdminService).
 */
class DeletePresenter extends AdminBasePresenter
{
    private CategoryEntity $category;

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
        $this->category = $this->em->find(CategoryEntity::class, $id) ?? $this->error('Category was not found.');
    }

    public function renderDefault() : void
    {
        $this->template->category = $this->category;
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
        $form->addSubmit('delete', 'admin-category-delete.confirm')
            ->setHtmlAttribute('class', 'btn btn-danger');

        $form->onSuccess[] = function (Form $form) : void {
            $name = $this->category->name;

            try {
                $this->forumAdmin->deleteCategory($this->category);
            } catch (AdminException $exception) {
                $form->addError($exception->getMessage());

                return;
            }

            $this->adminFlash($this->translator->translate('admin-category-delete.success', ['name' => $name]));
            $this->redirect(':Admin:Category:List:default');
        };

        return $form;
    }
}
