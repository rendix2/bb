<?php declare(strict_types=1);

namespace App\UI\Admin\Faq\Edit;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\FaqEntity;
use App\UI\Admin\Accessory\FaqFormFactory;
use App\UI\Admin\AdminBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use Nette\Localization\Translator;

/**
 * Editing an item of the FAQ (the texts in all languages and the order).
 */
class EditPresenter extends AdminBasePresenter
{
    private FaqEntity $faq;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly FaqFormFactory $faqFormFactory,
        private readonly MenuComponentFactory $menuFactory,
        private readonly Translator $translator,
    ) {
        parent::__construct();
    }

    public function actionDefault(int $id) : void
    {
        $this->faq = $this->em->find(FaqEntity::class, $id) ?? $this->error('Question was not found.');
    }

    protected function createComponentMenu() : MenuComponent
    {
        return $this->menuFactory->create('admin');
    }

    protected function createComponentFaqForm() : BootstrapForm
    {
        return $this->faqFormFactory->create($this->faq, function () : void {
            $this->adminFlash($this->translator->translate('admin-faq-edit.success'));
            $this->redirect(':Admin:Faq:List:default');
        });
    }
}
