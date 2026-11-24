<?php declare(strict_types=1);

namespace App\UI\Admin\Language\Delete;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\LanguageEntity;
use App\Model\Service\AdminException;
use App\Model\Service\LanguageAdminService;
use App\UI\Admin\AdminBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use Nette\Application\UI\Form;
use Nette\Localization\Translator;

/**
 * Deleting a language: a confirmation page with a POST form (CSRF); not one used by a user, not the default one (LanguageAdminService).
 */
class DeletePresenter extends AdminBasePresenter
{
    private LanguageEntity $language;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly LanguageAdminService $languageAdmin,
        private readonly MenuComponentFactory $menuFactory,
        private readonly Translator $translator,
    ) {
        parent::__construct();
    }

    public function actionDefault(int $id) : void
    {
        $this->language = $this->em->find(LanguageEntity::class, $id) ?? $this->error('Language was not found.');
    }

    public function renderDefault() : void
    {
        $this->template->language = $this->language;
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
        $form->addSubmit('delete', 'admin-language-delete.confirm')
            ->setHtmlAttribute('class', 'btn btn-danger');

        $form->onSuccess[] = function (Form $form) : void {
            $name = $this->language->name;

            try {
                $this->languageAdmin->delete($this->language);
            } catch (AdminException $exception) {
                $form->addError($this->translator->translate($exception->getMessage(), $exception->parameters), false);

                return;
            }

            $this->adminFlash($this->translator->translate('admin-language-delete.success', ['name' => $name]));
            $this->redirect(':Admin:Language:List:default');
        };

        return $form;
    }
}
