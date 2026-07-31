<?php

namespace App\Model\Repository;

use App\Model\Entity\SessionEntity;
use Doctrine\ORM\EntityRepository;
use Nette\Http\Session;

/**
 * class SessionRepository
 *
 * @package App\Model\Repository
 * @extends EntityRepository<SessionEntity>
 */
class SessionRepository extends EntityRepository
{
    /**
     * @return SessionEntity[]
     */
    public function findLoggedInUsers(): array
    {
        return $this->createQueryBuilder('_s')

        ->addSelect('_u')
        ->innerJoin('_s.user', '_u')

        ->groupBy('_u.id')
        ->orderBy('_s.lastActivity', 'DESC')

        ->getQuery()
        ->getResult();
    }

    public function findCountOfLoggedUsers(): int
    {
        return $this->createQueryBuilder('s')

            ->select('COUNT(DISTINCT s.user)')

            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findBySession(Session $session): array
    {
        return $this
            ->findBy(
                [
                    'key' => $session->getId(),
                ]
            );
    }

}