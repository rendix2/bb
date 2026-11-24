<?php declare(strict_types=1);

namespace App\UI\Forum\Moderation\Reports;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ReportEntity;
use App\Model\Entity\UserEntity;
use App\Model\Service\ReportService;
use App\UI\Forum\Accessory\ReportHandleFormFactory;
use App\UI\Forum\Accessory\ReportListFactory;
use App\UI\Forum\ForumBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Nette\Application\UI\Multiplier;
use Nette\Http\IResponse;

/**
 * The reports of posts for the moderators (slice 6): a moderator sees only the reports of the forums he moderates, the admin
 * all of them; open or handled ones. An open report is resolved or rejected by a POST form (CSRF) with a note for the
 * moderators; the reporter gets a private message (ReportService::handle()).
 */
class ReportsPresenter extends ForumBasePresenter
{
    public const string FILTER_OPEN = ReportListFactory::FILTER_OPEN;
    public const string FILTER_HANDLED = ReportListFactory::FILTER_HANDLED;

    private UserEntity $viewer;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ReportService $reportService,
        private readonly ReportListFactory $reportListFactory,
        private readonly ReportHandleFormFactory $reportHandleFormFactory,
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
        $this->template->setParameters($this->reportListFactory->create($this->viewer, $status, $page));
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

            return $this->reportHandleFormFactory->create($this->viewer, $report, function () : void {
                $this->forumFlash('forum-moderation-reports.handled', 'success');
                $this->redirect('this');
            });
        });
    }
}
