<?php declare(strict_types=1);

namespace App\UI\Admin\Forum\Create;

use App\Model\Entity\ForumEntity;
use App\UI\Admin\Accessory\ForumFormFactory;
use App\UI\Admin\AdminBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use Nette\Localization\Translator;

/**
 * A new forum.
 */
class CreatePresenter extends AdminBasePresenter
{
    public function __construct(
        private readonly ForumFormFactory $forumFormFactory,
        private readonly MenuComponentFactory $menuFactory,
        private readonly Translator $translator,
    ) {
        parent::__construct();
    }

    protected function createComponentMenu() : MenuComponent
    {
        return $this->menuFactory->create('admin');
    }

    protected function createComponentForumForm() : BootstrapForm
    {
        return $this->forumFormFactory->create(null, function (ForumEntity $forum) : void {
            $this->adminFlash($this->translator->translate('admin-forum-create.success', ['name' => $forum->name]));
            $this->redirect(':Admin:Forum:List:default');
        });
    }
}
