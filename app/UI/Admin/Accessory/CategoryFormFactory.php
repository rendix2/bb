<?php declare(strict_types=1);

namespace App\UI\Admin\Accessory;

use App\Model\Entity\CategoryEntity;
use App\Model\Service\AdminException;
use App\Model\Service\ForumAdminService;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Nette\Application\UI\Form;
use Nette\Localization\Translator;

/**
 * The form of a category for Admin:Category:Create and Admin:Category:Edit.
 */
class CategoryFormFactory
{
    public function __construct(
        private readonly ForumAdminService $forumAdmin,
        private readonly Translator $translator,
    ) {
    }

    /**
     * @param callable(CategoryEntity) : void $onSaved
     */
    public function create(?CategoryEntity $category, callable $onSaved) : BootstrapForm
    {
        $form = new BootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);

        $form->setTranslator($this->translator);
        $form->addProtection('admin-forum.protection');

        $form->addText('name', 'admin-forum.category.name')
            ->setRequired('admin-forum.error.name')
            ->setMaxLength(255);
        $form->addInteger('sortOrder', 'admin-forum.sortOrder')
            ->setRequired()
            ->setDefaultValue(0);
        $form->addCheckbox('active', 'admin-forum.active')
            ->setDefaultValue(true);
        $form->addSubmit('send', 'admin-forum.save');

        if ($category !== null) {
            $form->setDefaults([
                'name' => $category->name,
                'sortOrder' => $category->sortOrder,
                'active' => $category->active,
            ]);
        }

        $form->onSuccess[] = function (Form $form) use ($category, $onSaved) : void {
            $values = $form->getValues(CategoryValues::class);

            try {
                $saved = $this->forumAdmin->saveCategory($category, $values->name, $values->sortOrder, $values->active);
            } catch (AdminException $exception) {
                $form->addError($exception->getMessage());

                return;
            }

            $onSaved($saved);
        };

        return $form;
    }
}
