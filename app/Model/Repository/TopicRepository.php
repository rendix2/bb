<?php

namespace App\Model\Repository;

use App\Model\Entity\ForumEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use Doctrine\ORM\EntityRepository;

/**
 * class TopicRepository
 *
 * @package App\Model\Repository
 * @extends EntityRepository<TopicEntity>
 */
class TopicRepository extends EntityRepository
{

    public function findByForumId(int $forumId): array
    {
        return $this->findBy(
            [
                'forum' => $forumId
            ]
        );
    }

    public function findByForum(ForumEntity $forumEntity): array
    {
        return $this->findBy(
            [
                'forum' => $forumEntity,
            ]
        );
    }

    public function findByUser(UserEntity $userEntity): array
    {
        return $this->findBy(
            [
                'forum' => $userEntity,
            ]
        );
    }

    public function findPairs(): array
    {
        return $this->createQueryBuilder('_t')
            ->select('_t.id')
            ->addSelect('_t.name')

            ->getQuery()
            ->getArrayResult();
    }

}