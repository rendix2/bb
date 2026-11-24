<?php declare(strict_types=1);

namespace App\UI\Admin\Faq\Delete;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\FaqEntity;
use App\Model\Service\FaqService;
use App\UI\Admin\AdminBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use Nette\Application\UI\Form;
use Nette\Localization\Translator;

/**
 * Deleting an item of the FAQ with the texts in all languages: a confirmation page with a POST form (CSRF).
 */
class DeletePresenter extends AdminBasePresenter
{
    private FaqEntity $faq;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly FaqService $faqService,
        private readonly MenuComponentFactory $menuFactory,
        private readonly Translator $translator,
    ) {
        parent::__construct();
    }

    public function actionDefault(int $id) : void
    {
        $this->faq = $this->em->find(FaqEntity::class, $id) ?? $this->error('Question was not found.');
    }

    public function renderDefault() : void
    {
        $this->template->texts = $this->faqService->texts($this->faq);
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
        $form->addSubmit('delete', 'admin-faq-delete.confirm')
            ->setHtmlAttribute('class', 'btn btn-danger');

        $form->onSuccess[] = function (Form $form) : void {
            $this->faqService->delete($this->faq);
            $this->adminFlash($this->translator->translate('admin-faq-delete.success'));
            $this->redirect(':Admin:Faq:List:default');
        };

        return $form;
    }
}
