<?php declare(strict_types=1);

namespace App\UI\Admin\Faq\List;

use App\Model\Service\FaqService;
use App\UI\Admin\AdminBasePresenter;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use Contributte\Translation\Translator;

/**
 * The FAQ (slice 7e2): the items in the order, with the question in the language of the admin.
 */
class ListPresenter extends AdminBasePresenter
{
    public function __construct(
        private readonly FaqService $faqService,
        private readonly MenuComponentFactory $menuFactory,
        private readonly Translator $translator,
    ) {
        parent::__construct();
    }

    public function renderDefault() : void
    {
        $this->template->items = $this->faqService->forLocale($this->translator->getLocale());
    }

    protected function createComponentMenu() : MenuComponent
    {
        return $this->menuFactory->create('admin');
    }
}
