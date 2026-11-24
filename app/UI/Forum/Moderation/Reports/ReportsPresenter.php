<?php declare(strict_types=1);

namespace App\UI\Forum\Moderation\Reports;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ReportEntity;
use App\Model\Entity\UserEntity;
use App\Model\Service\PostWriteException;
use App\Model\Service\ReportService;
use App\Model\Service\TopicPaginationService;
use App\Settings\ForumSettings;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Nette\Application\UI\Presenter;
use Nette\Http\IResponse;
use Nette\Localization\Translator;
use Nette\Utils\Paginator;

/**
 * The reports of posts for the moderators (slice 6): a moderator sees only the reports of the forums he moderates, the admin
 * all of them; open or handled ones. An open report is resolved or rejected by a POST form (CSRF) with a note for the
 * moderators; the reporter gets a private message (ReportService::handle()).
 */
class ReportsPresenter extends Presenter
{
    public const string FILTER_OPEN = 'open';
    public const string FILTER_HANDLED = 'handled';

    private UserEntity $viewer;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ReportService $reportService,
        private readonly TopicPaginationService $topicPagination,
        private readonly ForumSettings $forumSettings,
        private readonly Translator $translator,
    ) {
        parent::__construct();
    }

    public function actionDefault(string $status = self::FILTER_OPEN, int $page = 1) : void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect(':Web:User:Login:default', ['backlink' => $this->storeRequest()]);
        }

        $viewer = $this->em->find(UserEntity::class, $this->getUser()->getId());

        if ($viewer === null || !$this->reportService->isModerator($viewer)) {
            $this->error('Only moderators see the reports.', IResponse::S403_Forbidden);
        }

        $this->viewer = $viewer;
    }

    public function renderDefault(string $status = self::FILTER_OPEN, int $page = 1) : void
    {
        $open = $status !== self::FILTER_HANDLED;

        $paginator = new Paginator();
        $paginator->setItemsPerPage((int) $this->forumSettings->get()['pagination']['itemsPerPage']);
        $paginator->setItemCount($this->reportService->countFor($this->viewer, $open));
        $paginator->setPage($page);

        $reports = $this->reportService->reportsFor($this->viewer, $open, $paginator->getOffset(), $paginator->getLength());
        $pages = [];

        foreach ($reports as $report) {
            $pages[$report->id] = $this->topicPagination->pageOf($report->post);
        }

        $this->template->open = $open;
        $this->template->reports = $reports;
        $this->template->pages = $pages;
        $this->template->openCount = $open ? $paginator->getItemCount() : $this->reportService->countFor($this->viewer, true);
        $this->template->paginator = $paginator;
        $this->template->statusResolved = ReportService::STATUS_RESOLVED;
    }

    /**
     * Handle one open report: one form per report id (the rights are checked again in the service).
     */
    protected function createComponentHandleForm() : Multiplier
    {
        return new Multiplier(function (string $reportId) : BootstrapForm {
            $report = $this->em->find(ReportEntity::class, (int) $reportId);

            if ($report === null || !$this->reportService->mayHandle($this->viewer, $report)) {
                $this->error('Report was not found.');
            }

            $form = new BootstrapForm();
            BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);

            $form->setTranslator($this->translator);
            $form->addProtection('forum-moderation-reports.form.protection');

            $form->addRadioList('status', 'forum-moderation-reports.form.status.label', [
                ReportService::STATUS_RESOLVED => 'forum-moderation-reports.status.resolved',
                ReportService::STATUS_REJECTED => 'forum-moderation-reports.status.rejected',
            ])->setRequired('forum-moderation-reports.form.status.required');
            $form->addTextArea('note', 'forum-moderation-reports.form.note.label', rows: 2)
                ->setMaxLength(ReportService::TEXT_MAX_LENGTH);
            $form->addSubmit('send', 'forum-moderation-reports.form.send')
                ->setHtmlAttribute('class', 'btn btn-outline-primary btn-sm');

            $form->onSuccess[] = function (Form $form) use ($report) : void {
                $values = $form->getValues('array');

                try {
                    $this->reportService->handle($this->viewer, $report, (int) $values['status'], (string) $values['note']);
                } catch (PostWriteException $exception) {
                    $form->addError($exception->getMessage());

                    return;
                }

                $this->flashMessage('forum-moderation-reports.handled', 'success');
                $this->redirect('this');
            };

            return $form;
        });
    }
}
