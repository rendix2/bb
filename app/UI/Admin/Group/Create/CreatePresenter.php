<?php declare(strict_types=1);

namespace App\UI\Admin\Group\Create;

use App\Model\Entity\GroupEntity;
use App\UI\Admin\Accessory\GroupFormFactory;
use App\UI\Admin\AdminBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use Nette\Localization\Translator;

/**
 * A new group.
 */
class CreatePresenter extends AdminBasePresenter
{
    public function __construct(
        private readonly GroupFormFactory $groupFormFactory,
        private readonly MenuComponentFactory $menuFactory,
        private readonly Translator $translator,
    ) {
        parent::__construct();
    }

    protected function createComponentMenu() : MenuComponent
    {
        return $this->menuFactory->create('admin');
    }

    protected function createComponentGroupForm() : BootstrapForm
    {
        return $this->groupFormFactory->create(null, function (GroupEntity $group) : void {
            $this->adminFlash($this->translator->translate('admin-group-create.success', ['name' => $group->name]));
            // next: its members and access to the forums
            $this->redirect(':Admin:Group:Edit:default', ['id' => $group->id]);
        });
    }
}
