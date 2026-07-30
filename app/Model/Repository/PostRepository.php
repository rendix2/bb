<?php

namespace App\Model\Repository;

use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use Doctrine\ORM\EntityRepository;

/**
 * class PostRepository
 *
 * @package App\Model\Repository
 * @extends EntityRepository<PostEntity>
 */
class PostRepository extends EntityRepository
{
    public function findByUserId(int $userId): array
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

    public function findByUser(UserEntity $userEntity): array
    {
        /**
         * @var PostEntity[]
         */
        return $this->findBy(
            [
                'user' => $userEntity,
            ]
        );
    }

    public function findByTopicId(int $topicId): array
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

    public function findByTopic(TopicEntity $topicEntity): array
    {
        /**
         * @var PostEntity[]
         */
        return $this->findBy(
            [
                'topic' => $topicEntity,
            ]
        );
    }

    public function findFirstByTopicId(int $topicId): ?PostEntity
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

    public function findLastByTopicId(int $topicId): ?PostEntity
    {
        $qb = $this->createQueryBuilder('p');
        $subQb = $this->createQueryBuilder('p2');

        $subQb->select('MAX(p2.id)')
            ->where('p2.topic = :topicId');

        return $qb->where($qb->expr()->eq('p.id', '(' . $subQb->getDQL() . ')'))
            ->setParameter('topicId', $topicId)

            ->getQuery()
            ->getOneOrNullResult();
    }


    public function findLastByForumId(int $forumId): ?PostEntity
    {
        $qb = $this->createQueryBuilder('_p');
        $subQb = $this->createQueryBuilder('_p2');

        $subQb->select('MAX(_p2.id)')
            ->where('_p2.forum = :forumId');

        return $qb->where($qb->expr()->eq('_p.id', '(' . $subQb->getDQL() . ')'))
            ->setParameter('forumId', $forumId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findFirstByForumId(int $forumId): ?PostEntity
    {
        $qb = $this->createQueryBuilder('_p');
        $subQb = $this->createQueryBuilder('_p2');

        $subQb->select('MIN(_p2.id)')
            ->where('_p2.forum = :forumId');

        return $qb->where($qb->expr()->eq('_p.id', '(' . $subQb->getDQL() . ')'))
            ->setParameter('forumId', $forumId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findNewerPosts(int $forum_id, \DateTimeImmutable $post_time): array
    {
        return $this->createQueryBuilder('_p')

            ->where('_p.forum = :forum')
            ->setParameter('forum', $forum_id)

            ->andWhere('_p.createdAt > :createdAt')
            ->setParameter('createdAt', $post_time)

            ->getQuery()
            ->getResult();
    }

}