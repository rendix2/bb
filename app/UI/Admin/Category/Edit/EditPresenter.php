<?php declare(strict_types=1);

namespace App\UI\Admin\Category\Edit;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\CategoryEntity;
use App\UI\Admin\Accessory\CategoryFormFactory;
use App\UI\Admin\AdminBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use Nette\Localization\Translator;

/**
 * Editing a category.
 */
class EditPresenter extends AdminBasePresenter
{
    private CategoryEntity $category;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly CategoryFormFactory $categoryFormFactory,
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

    protected function createComponentCategoryForm() : BootstrapForm
    {
        return $this->categoryFormFactory->create($this->category, function (CategoryEntity $category) : void {
            $this->adminFlash($this->translator->translate('admin-category-edit.success', ['name' => $category->name]));
            $this->redirect(':Admin:Category:List:default');
        });
    }
}
