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

}