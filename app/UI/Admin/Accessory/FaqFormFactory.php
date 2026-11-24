<?php declare(strict_types=1);

namespace App\UI\Admin\Accessory;

use App\Model\Entity\FaqEntity;
use App\Model\Service\AdminException;
use App\Model\Service\FaqService;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Nette\Application\UI\Form;
use Nette\Localization\Translator;

/**
 * The form of an item of the FAQ (Admin:Faq:Create, Admin:Faq:Edit): the order and a question and an answer for each language
 * (container texts[<language ID>][question|answer]).
 */
class FaqFormFactory
{
    public function __construct(
        private readonly FaqService $faqService,
        private readonly Translator $translator,
    ) {
    }

    /**
     * @param callable(FaqEntity) : void $onSaved
     */
    public function create(?FaqEntity $faq, callable $onSaved) : BootstrapForm
    {
        $form = new BootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);
        $form->setTranslator($this->translator);
        $form->addProtection('admin-forum.protection');

        $existing = $faq === null ? [] : $this->faqService->texts($faq);
        $texts = $form->addContainer('texts');

        foreach ($this->faqService->languages() as $language) {
            $container = $texts->addContainer((string) $language->id);
            $container->addText('question', $this->translator->translate('admin-faq.question', ['language' => $language->name]))
                ->setTranslator(null)
                ->setRequired($this->translator->translate('admin-faq.error.question', ['language' => $language->name]))
                ->setDefaultValue($existing[$language->id]->question ?? null);
            $container->addTextArea('answer', $this->translator->translate('admin-faq.answer', ['language' => $language->name]), rows: 6)
                ->setTranslator(null)
                ->setRequired($this->translator->translate('admin-faq.error.answer', ['language' => $language->name]))
                ->setDefaultValue($existing[$language->id]->answer ?? null);
        }

        $form->addInteger('sortOrder', 'admin-forum.sortOrder')
            ->setRequired()
            ->setDefaultValue($faq->sortOrder ?? 0);
        $form->addSubmit('send', 'admin-forum.save');

        $form->onSuccess[] = function (Form $form) use ($faq, $onSaved) : void {
            /** @var array{texts: array<int, array{question: string, answer: string}>, sortOrder: int} $values */
            $values = $form->getValues('array');

            try {
                $saved = $this->faqService->save($faq, $values['sortOrder'], $values['texts']);
            } catch (AdminException $exception) {
                $form->addError($this->translator->translate($exception->getMessage(), $exception->parameters), false);

                return;
            }

            $onSaved($saved);
        };

        return $form;
    }
}
