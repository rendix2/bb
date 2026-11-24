<?php declare(strict_types=1);

namespace App\UI\Web\Banned;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\BanEntity;
use DateTimeImmutable;
use Nette\Application\UI\Presenter;

/**
 * The page of a ban (BanGuard sends the banned visitor here): the reason and until when. Only an active ban is shown.
 */
class BannedPresenter extends Presenter
{
    public function __construct(
        private readonly EntityManagerDecorator $em,
    ) {
        parent::__construct();
    }

    public function renderDefault(string $uuid) : void
    {
        $ban = $this->em->getRepository(BanEntity::class)->findOneBy(['uuid' => $uuid]);

        if ($ban === null || !$ban->isActive(new DateTimeImmutable())) {
            $this->error('Ban was not found.');
        }

        $this->getHttpResponse()->setCode(403);
        $this->template->ban = $ban;
    }
}
