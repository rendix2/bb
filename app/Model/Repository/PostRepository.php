<?php

namespace App\Model\Repository;

use App\Model\Entity\PostEntity;
use Doctrine\ORM\EntityRepository;

/**
 * class PostRepository
 *
 * @package App\Model\Repository
 * @extends EntityRepository<PostEntity>
 */
class PostRepository extends EntityRepository
{
    public function findByUser(int $userId): array
    {
        /**
         * @var PostEntity[]
         */
        return $this->findBy(
            [
                'user' => $userId,
            ]
        );
    }

    public function findByTopic(int $topicId): array
    {
        /**
         * @var PostEntity[]
         */
        return $this->findBy(
            [
                'topic' => $topicId,
            ]
        );
    }

    public function getFirstByTopic(int $topicId): ?PostEntity
    {
        $qb = $this->createQueryBuilder('p');
        $subQb = $this->createQueryBuilder('p2');

        $subQb->select('MIN(p2.id)')
            ->where('p2.topic = :topicId');

        return $qb->where($qb->expr()->eq('p.id', '(' . $subQb->getDQL() . ')'))
            ->setParameter('topicId', $topicId)

            ->getQuery()
            ->getOneOrNullResult();
    }

}