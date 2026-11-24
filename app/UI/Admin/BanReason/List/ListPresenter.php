<?php declare(strict_types=1);

namespace App\UI\Admin\BanReason\List;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\BanReasonEntity;
use App\UI\Admin\Accessory\BanReasonFormFactory;
use App\UI\Admin\AdminBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use Nette\Localization\Translator;

/**
 * The predefined reasons of bans (slice 7c1): the list and a form to add one.
 */
class ListPresenter extends AdminBasePresenter
{
    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly BanReasonFormFactory $banReasonFormFactory,
        private readonly MenuComponentFactory $menuFactory,
        private readonly Translator $translator,
    ) {
        parent::__construct();
    }

    public function renderDefault() : void
    {
        $this->template->reasons = $this->em->getRepository(BanReasonEntity::class)->findBy([], ['sortOrder' => 'ASC', 'id' => 'ASC']);
    }

    protected function createComponentMenu() : MenuComponent
    {
        return $this->menuFactory->create('admin');
    }

    protected function createComponentReasonForm() : BootstrapForm
    {
        return $this->banReasonFormFactory->create(null, function () : void {
            $this->adminFlash($this->translator->translate('admin-ban-reason-list.created'));
            $this->redirect('this');
        });
    }
}
