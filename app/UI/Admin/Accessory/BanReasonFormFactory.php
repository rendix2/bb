<?php declare(strict_types=1);

namespace App\UI\Admin\Accessory;

use App\Model\Entity\BanReasonEntity;
use App\Model\Service\AdminException;
use App\Model\Service\BanAdminService;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Nette\Application\UI\Form;
use Nette\Localization\Translator;

/**
 * The form of a predefined reason of a ban (Admin:BanReason:List adds, Admin:BanReason:Edit edits).
 */
class BanReasonFormFactory
{
    public function __construct(
        private readonly BanAdminService $banAdmin,
        private readonly Translator $translator,
    ) {
    }

    /**
     * @param callable(BanReasonEntity) : void $onSaved
     */
    public function create(?BanReasonEntity $reason, callable $onSaved) : BootstrapForm
    {
        $form = new BootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);
        $form->setTranslator($this->translator);
        $form->addProtection('admin-forum.protection');

        $form->addTextArea('text', 'admin-ban.reason.text', rows: 2)
            ->setRequired('admin-ban.error.reason')
            ->setDefaultValue($reason?->text);
        $form->addInteger('sortOrder', 'admin-forum.sortOrder')
            ->setRequired()
            ->setDefaultValue($reason->sortOrder ?? 0);
        $form->addSubmit('send', 'admin-forum.save');

        $form->onSuccess[] = function (Form $form) use ($reason, $onSaved) : void {
            /** @var array{text: string, sortOrder: int} $values */
            $values = $form->getValues('array');

            try {
                $saved = $this->banAdmin->saveReason($reason, $values['text'], $values['sortOrder']);
            } catch (AdminException $exception) {
                $form->addError($exception->getMessage());

                return;
            }

            $onSaved($saved);
        };

        return $form;
    }
}
