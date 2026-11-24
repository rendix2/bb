<?php declare(strict_types=1);

namespace App\UI\Admin\Rank\Delete;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\RankEntity;
use App\Model\Service\AdminException;
use App\Model\Service\RankAdminService;
use App\UI\Admin\AdminBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use Nette\Application\UI\Form;
use Nette\Localization\Translator;

/**
 * Deleting a rank: a confirmation page with a POST form (CSRF); users with this special rank fall back to the rank by posts.
 */
class DeletePresenter extends AdminBasePresenter
{
    private RankEntity $rank;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly RankAdminService $rankAdmin,
        private readonly MenuComponentFactory $menuFactory,
        private readonly Translator $translator,
    ) {
        parent::__construct();
    }

    public function actionDefault(int $id) : void
    {
        $this->rank = $this->em->find(RankEntity::class, $id) ?? $this->error('Rank was not found.');
    }

    public function renderDefault() : void
    {
        $this->template->rank = $this->rank;
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
        $form->addSubmit('delete', 'admin-rank-delete.confirm')
            ->setHtmlAttribute('class', 'btn btn-danger');

        $form->onSuccess[] = function (Form $form) : void {
            $name = $this->rank->name;

            try {
                $this->rankAdmin->delete($this->rank);
            } catch (AdminException $exception) {
                $form->addError($exception->getMessage());

                return;
            }

            $this->adminFlash($this->translator->translate('admin-rank-delete.success', ['name' => $name]));
            $this->redirect(':Admin:Rank:List:default');
        };

        return $form;
    }
}
