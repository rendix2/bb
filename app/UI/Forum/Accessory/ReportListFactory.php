<?php declare(strict_types=1);

namespace App\UI\Forum\Accessory;

use App\Model\Entity\UserEntity;
use App\Model\Enum\ReportStatus;
use App\Model\Service\ReportService;
use App\Model\Service\TopicPaginationService;
use App\Settings\ForumSettings;
use Nette\Utils\Paginator;

/**
 * The parameters of the template of the reports (Forum/Moderation/Reports/default.latte) for a viewer: one page of open or
 * handled reports, the pages of the reported posts, the count of the open ones. Used by Forum:Moderation:Reports and
 * Admin:Report:List (the same list, the admin sees all forums).
 */
class ReportListFactory
{
    public const string FILTER_OPEN = 'open';
    public const string FILTER_HANDLED = 'handled';

    public function __construct(
        private readonly ReportService $reportService,
        private readonly TopicPaginationService $topicPagination,
        private readonly ForumSettings $forumSettings,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function create(UserEntity $viewer, string $status, int $page) : array
    {
        $open = $status !== self::FILTER_HANDLED;

        $paginator = new Paginator();
        $paginator->setItemsPerPage((int) $this->forumSettings->get()['pagination']['itemsPerPage']);
        $paginator->setItemCount($this->reportService->countFor($viewer, $open));
        $paginator->setPage($page);

        $reports = $this->reportService->reportsFor($viewer, $open, $paginator->getOffset(), $paginator->getLength());
        $pages = [];

        foreach ($reports as $report) {
            $pages[$report->id] = $this->topicPagination->pageOf($report->post);
        }

        return [
            'open' => $open,
            'reports' => $reports,
            'pages' => $pages,
            'openCount' => $open ? $paginator->getItemCount() : $this->reportService->countFor($viewer, true),
            'paginator' => $paginator,
            'statusResolved' => ReportStatus::Resolved,
        ];
    }
}
