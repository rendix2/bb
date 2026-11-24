<?php declare(strict_types=1);

namespace App\UI\Admin\Category\Create;

use App\Model\Entity\CategoryEntity;
use App\UI\Admin\Accessory\CategoryFormFactory;
use App\UI\Admin\AdminBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use Nette\Localization\Translator;

/**
 * A new category.
 */
class CreatePresenter extends AdminBasePresenter
{
    public function __construct(
        private readonly CategoryFormFactory $categoryFormFactory,
        private readonly MenuComponentFactory $menuFactory,
        private readonly Translator $translator,
    ) {
        parent::__construct();
    }

    protected function createComponentMenu() : MenuComponent
    {
        return $this->menuFactory->create('admin');
    }

    protected function createComponentCategoryForm() : BootstrapForm
    {
        return $this->categoryFormFactory->create(null, function (CategoryEntity $category) : void {
            $this->adminFlash($this->translator->translate('admin-category-create.success', ['name' => $category->name]));
            $this->redirect(':Admin:Category:List:default');
        });
    }
}
