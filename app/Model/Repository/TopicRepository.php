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

    public function getFirstByForum(int $forumId): ?TopicEntity
    {
        $qb = $this->createQueryBuilder('_t');
        $subQb = $this->createQueryBuilder('_t2');

        $subQb->select('MIN(_t2.id)')
            ->where('_t2.forum = :forumId');

        return $qb->where($qb->expr()->eq('_t.id', '(' . $subQb->getDQL() . ')'))
            ->setParameter('forumId', $forumId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLastByForumId(int $forumId): ?TopicEntity
    {
        $qb = $this->createQueryBuilder('_t');
        $subQb = $this->createQueryBuilder('_t2');

        $subQb->select('MAX(_t2.id)')
            ->where('_t2.forum = :forumId');

        return $qb->where($qb->expr()->eq('_t.id', '(' . $subQb->getDQL() . ')'))
            ->setParameter('forumId', $forumId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findNewerTopics(int $forum_id, \DateTimeImmutable $topic_time): array
    {
        return $this->createQueryBuilder('_t')

            ->where('_t.forum = :forum')
            ->setParameter('forum', $forum_id)

            ->andWhere('_t.createdAt > :createdAt')
            ->setParameter('createdAt', $topic_time)

            ->getQuery()
            ->getResult();
    }

}