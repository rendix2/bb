<?php

namespace App\Model\Repository;

use App\Model\Entity\ForumEntity;
use Doctrine\ORM\EntityRepository;

/**
 * class ForumRepository
 *
 * @package App\Model\Repository
 * @extends EntityRepository<ForumEntity>
 */
class ForumRepository extends EntityRepository
{


    /**
     * @param int $id
     *
     * @return ForumEntity[]
     */
    public function findByCategoryId(int $id) : array
    {
        return $this->findBy(
            [
                'category' => $id,
            ]
        );
    }

    /**
     * @param int $id
     *
     * @return ForumEntity[]
     */
    public function findByParentId(int $id): array
    {
        return $this->findBy(
            [
                'parent' => $id,
            ]
        );
    }

    public function findPairs(): array
    {
        return $this->createQueryBuilder('_f')
            ->select('_f.id')
            ->addSelect('_f.name')

            ->getQuery()
            ->getArrayResult();
    }

}