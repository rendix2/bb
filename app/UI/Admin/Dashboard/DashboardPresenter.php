<?php declare(strict_types=1);

namespace App\UI\Admin\Dashboard;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\BanEntity;
use App\Model\Entity\MailEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\ReportEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use App\Model\Enum\ReportStatus;
use App\UI\Admin\AdminBasePresenter;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use DateTimeImmutable;

/**
 * The overview of the administration (slice 7e1, for now simple statistics, user decision 2026-11-24).
 */
class DashboardPresenter extends AdminBasePresenter
{
    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly MenuComponentFactory $menuFactory,
    ) {
        parent::__construct();
    }

    public function renderDefault() : void
    {
        $activeBans = (int) $this->em->getRepository(BanEntity::class)
            ->createQueryBuilder('_b')
            ->select('COUNT(_b.id)')
            ->where('_b.expiresAt IS NULL OR _b.expiresAt > :now')
            ->setParameter('now', new DateTimeImmutable())
            ->getQuery()
            ->getSingleScalarResult();

        $this->template->stats = [
            'users' => $this->em->getRepository(UserEntity::class)->count([]),
            'activeUsers' => $this->em->getRepository(UserEntity::class)->count(['isActive' => true]),
            'topics' => $this->em->getRepository(TopicEntity::class)->count([]),
            'posts' => $this->em->getRepository(PostEntity::class)->count([]),
            'openReports' => $this->em->getRepository(ReportEntity::class)->count(['status' => ReportStatus::Open]),
            'activeBans' => $activeBans,
            // only the mails not sent yet; the sent ones stay in the table as the history
            'mailQueue' => $this->em->getRepository(MailEntity::class)->count(['sentAt' => null]),
        ];
    }

    protected function createComponentMenu() : MenuComponent
    {
        return $this->menuFactory->create('admin');
    }
}
