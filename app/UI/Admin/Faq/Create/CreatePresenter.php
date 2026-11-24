<?php declare(strict_types=1);

namespace App\UI\Admin\Faq\Create;

use App\Model\Entity\FaqEntity;
use App\UI\Admin\Accessory\FaqFormFactory;
use App\UI\Admin\AdminBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use Nette\Localization\Translator;

/**
 * Adding an item of the FAQ.
 */
class CreatePresenter extends AdminBasePresenter
{
    public function __construct(
        private readonly FaqFormFactory $faqFormFactory,
        private readonly MenuComponentFactory $menuFactory,
        private readonly Translator $translator,
    ) {
        parent::__construct();
    }

    protected function createComponentMenu() : MenuComponent
    {
        return $this->menuFactory->create('admin');
    }

    protected function createComponentFaqForm() : BootstrapForm
    {
        return $this->faqFormFactory->create(null, function (FaqEntity $faq) : void {
            $this->adminFlash($this->translator->translate('admin-faq-create.success'));
            $this->redirect(':Admin:Faq:List:default');
        });
    }
}
