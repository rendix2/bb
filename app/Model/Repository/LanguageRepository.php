<?php

namespace App\Model\Repository;

use App\Model\Entity\LanguageEntity;
use Doctrine\ORM\EntityRepository;

/**
 * class ForumRepository
 *
 * @package App\Model\Repository
 * @extends EntityRepository<LanguageEntity>
 */
class LanguageRepository extends EntityRepository
{
    public function findPairs(): array
    {
        return $this->createQueryBuilder('_l')
            ->select('_l.id')
            ->addSelect('_l.name')

            ->getQuery()
            ->getArrayResult();
    }

}