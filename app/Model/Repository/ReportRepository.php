<?php declare(strict_types=1);

namespace App\Model\Repository;

use App\Model\Entity\ForumEntity;
use App\Model\Entity\ReportEntity;
use Doctrine\ORM\EntityRepository;

/**
 * class ReportRepository
 *
 * @package App\Model\Repository
 * @extends EntityRepository<ReportEntity>
 */
class ReportRepository extends EntityRepository
{

    public function findByForum(ForumEntity $forumEntity): array
    {
        return $this->findBy(
            [
                'forum' => $forumEntity,
            ]
        );
    }

    public function findByForumId(int $forumId): array
    {
        return $this->findBy(
            [
                'forum' => $forumId,
            ]
        );
    }
}