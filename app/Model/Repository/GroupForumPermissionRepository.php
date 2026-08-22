<?php declare(strict_types=1);

namespace App\Model\Repository;

use App\Model\Entity\GroupForumPermissionEntity;
use Doctrine\ORM\EntityRepository;

/**
 * class GroupForumPermissionRepository
 *
 * @package App\Model\Repository
 * @extends EntityRepository<GroupForumPermissionEntity>
 */
class GroupForumPermissionRepository extends EntityRepository
{
    /**
     * @return GroupForumPermissionEntity[]
     */
    public function findPermissionsByUserAndForum(int $userId, int $forumId): array
    {
        return $this->createQueryBuilder('_gfp')
            ->addSelect('_g')
            ->innerJoin('gfp.group', '_g')

            ->addSelect('_u')
            ->innerJoin('g.users', '_u')

            ->where('_u.id = :userId')
            ->setParameter('userId', $userId)

            ->andWhere('_gfp.forum = :forumId')
            ->setParameter('forumId', $forumId)

            ->getQuery()
            ->getResult();
    }
}