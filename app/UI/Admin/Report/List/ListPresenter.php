<?php declare(strict_types=1);

namespace App\UI\Admin\Report\List;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ReportEntity;
use App\Model\Entity\UserEntity;
use App\Model\Service\ReportService;
use App\UI\Admin\AdminBasePresenter;
use App\UI\Forum\Accessory\ReportHandleFormFactory;
use App\UI\Forum\Accessory\ReportListFactory;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use Nette\Application\UI\Multiplier;
use Nette\Localization\Translator;

/**
 * The reports of posts in the administration (slice 7e1, user decision: the admin sees them in the administration): the same
 * list and handling as Forum:Moderation:Reports (its template, ReportListFactory, ReportHandleFormFactory), the admin sees
 * all forums. Nobody deletes a report (user decision 2026-11-24).
 */
class ListPresenter extends AdminBasePresenter
{
    private UserEntity $viewer;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ReportService $reportService,
        private readonly ReportListFactory $reportListFactory,
        private readonly ReportHandleFormFactory $reportHandleFormFactory,
        private readonly MenuComponentFactory $menuFactory,
        private readonly Translator $translator,
    ) {
        parent::__construct();
    }

    public function actionDefault(string $status = ReportListFactory::FILTER_OPEN, int $page = 1) : void
    {
        $this->viewer = $this->admin();
    }

    public function renderDefault(string $status = ReportListFactory::FILTER_OPEN, int $page = 1) : void
    {
        $this->template->setFile(__DIR__ . '/../../../Forum/Moderation/Reports/default.latte');
        $this->template->setParameters($this->reportListFactory->create($this->viewer, $status, $page));
    }

    protected function createComponentMenu() : MenuComponent
    {
        return $this->menuFactory->create('admin');
    }

    protected function createComponentHandleForm() : Multiplier
    {
        return new Multiplier(function (string $reportId) : BootstrapForm {
            $report = $this->em->find(ReportEntity::class, (int) $reportId);

            if ($report === null || !$this->reportService->mayHandle($this->viewer, $report)) {
                $this->error('Report was not found.');
            }

            return $this->reportHandleFormFactory->create($this->viewer, $report, function () : void {
                $this->adminFlash($this->translator->translate('forum-moderation-reports.handled'));
                $this->redirect('this');
            });
        });
    }
}
