<?php declare(strict_types=1);

namespace App\UI\Forum\Faq;

use App\Model\Service\FaqService;
use Contributte\Translation\Translator;
use Nette\Application\UI\Presenter;

/**
 * The FAQ for everyone (slice 7e2), in the language of the reader (FaqService::forLocale()), the answers in Markdown.
 */
class FaqPresenter extends Presenter
{
    public function __construct(
        private readonly FaqService $faqService,
        private readonly Translator $translator,
    ) {
        parent::__construct();
    }

    public function renderDefault() : void
    {
        $this->template->items = $this->faqService->forLocale($this->translator->getLocale());
    }
}
