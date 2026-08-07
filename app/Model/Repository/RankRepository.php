<?php

namespace App\Model\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * class SessionRepository
 *
 * @package App\Model\Repository
 * @extends EntityRepository<RankRepository>
 */
class RankRepository extends EntityRepository
{

    public function findSpecialRanksPairs(): array
    {
        $this->createQueryBuilder('_r')
            ->select('_r.id')
            ->addSelect('_r.fileName')

            ->where('_r.isSpecial = :isSpecial')
            ->setParameter('isSpecial', true)

            ->getQuery()
            ->getArrayResult();
    }

}