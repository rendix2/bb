<?php

namespace App\Model\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * bb: used only by the old code (app/AdminModule, app/services/RankService), it goes with it (slice 7f)
 *
 * @package App\Model\Repository
 * @extends EntityRepository<\App\Model\Entity\RankEntity>
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