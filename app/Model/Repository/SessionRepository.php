<?php

namespace App\Model\Repository;

use App\Model\Entity\SessionEntity;
use Doctrine\ORM\EntityRepository;

/**
 * class UserRepository
 *
 * @package App\Model\Repository
 * @extends EntityRepository<SessionEntity>
 */
class SessionRepository extends EntityRepository
{
    /**
     * @return SessionEntity[]
     */
    public function getLoggedInUsers(): array
    {
        return $this->createQueryBuilder('_s')

        ->addSelect('_u')
        ->innerJoin('_s.user', '_u')

        ->groupBy('_u.id')
        ->orderBy('_s.lastActivity', 'DESC')

        ->getQuery()
        ->getResult();
    }

    public function getCountOfLoggedUsers(): int
    {
        return $this->createQueryBuilder('s')

            ->select('COUNT(DISTINCT s.user)')

            ->getQuery()
            ->getSingleScalarResult();
    }

}