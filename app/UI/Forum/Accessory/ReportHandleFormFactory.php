<?php declare(strict_types=1);

namespace App\UI\Forum\Accessory;

use App\Model\Entity\ReportEntity;
use App\Model\Entity\UserEntity;
use App\Model\Enum\ReportStatus;
use App\Model\Service\PostWriteException;
use App\Model\Service\ReportService;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Nette\Application\UI\Form;
use Nette\Localization\Translator;

/**
 * The form handling one open report (POST, CSRF): resolved or rejected with a note for the moderators; the rights are checked
 * again in ReportService::handle(). Used by Forum:Moderation:Reports and Admin:Report:List.
 */
class ReportHandleFormFactory
{
    public function __construct(
        private readonly ReportService $reportService,
        private readonly Translator $translator,
    ) {
    }

    /**
     * @param callable() : void $onHandled
     */
    public function create(UserEntity $viewer, ReportEntity $report, callable $onHandled) : BootstrapForm
    {
        $form = new BootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);

        $form->setTranslator($this->translator);
        $form->addProtection('forum-moderation-reports.form.protection');

        $results = [];

        foreach (ReportStatus::cases() as $status) {
            if ($status->isResult()) {
                $results[$status->value] = $status->translationKey();
            }
        }

        $form->addRadioList('status', 'forum-moderation-reports.form.status.label', $results)->setRequired('forum-moderation-reports.form.status.required');
        $form->addTextArea('note', 'forum-moderation-reports.form.note.label', rows: 2)
            ->setMaxLength(ReportService::TEXT_MAX_LENGTH);
        $form->addSubmit('send', 'forum-moderation-reports.form.send')
            ->setHtmlAttribute('class', 'btn btn-outline-primary btn-sm');

        $form->onSuccess[] = function (Form $form) use ($viewer, $report, $onHandled) : void {
            $values = $form->getValues('array');

            try {
                $this->reportService->handle($viewer, $report, ReportStatus::from((string) $values['status']), (string) $values['note']);
            } catch (PostWriteException $exception) {
                $form->addError($exception->getMessage());

                return;
            }

            $onHandled();
        };

        return $form;
    }
}
