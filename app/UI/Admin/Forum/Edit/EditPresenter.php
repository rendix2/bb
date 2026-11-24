<?php declare(strict_types=1);

namespace App\UI\Admin\Forum\Edit;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\UI\Admin\Accessory\ForumFormFactory;
use App\UI\Admin\AdminBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use Nette\Localization\Translator;

/**
 * Editing a forum.
 */
class EditPresenter extends AdminBasePresenter
{
    private ForumEntity $forum;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ForumFormFactory $forumFormFactory,
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

    protected function createComponentForumForm() : BootstrapForm
    {
        return $this->forumFormFactory->create($this->forum, function (ForumEntity $forum) : void {
            $this->adminFlash($this->translator->translate('admin-forum-edit.success', ['name' => $forum->name]));
            $this->redirect(':Admin:Forum:List:default');
        });
    }
}
