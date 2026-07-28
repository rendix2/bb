<?php

namespace App\Model\Repository;

use App\Model\Entity\CategoryEntity;
use Doctrine\ORM\EntityRepository;

/**
 * class ForumRepository
 *
 * @package App\Model\Repository
 * @extends EntityRepository<CategoryEntity>
 */
class CategoryRepository extends EntityRepository
{

    public function findPairs(): array
    {
        return $this->createQueryBuilder('_c')
            ->select('_c.id')
            ->addSelect('_c.name')

            ->getQuery()
            ->getArrayResult();
    }

}