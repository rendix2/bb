<?php declare(strict_types=1);

namespace App\Model\Repository;

use App\Model\Entity\ForumEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use DateTimeImmutable;
use Doctrine\ORM\EntityRepository;

/**
 * class TopicRepository
 *
 * @package App\Model\Repository
 * @extends EntityRepository<TopicEntity>
 */
class TopicRepository extends EntityRepository
{

    /**
     * One page of the topics of a forum with their author, number of posts and time of the last post; the topic with the
     * newest post first (a topic without posts by its creation).
     *
     * @return list<array{topic: TopicEntity, postCount: int, lastPostAt: DateTimeImmutable}>
     */
    public function findPageOfForum(ForumEntity $forum, int $offset, int $limit) : array
    {
        $rows = $this->createQueryBuilder('_t')
            ->select('_t', '_u')
            ->addSelect('COUNT(_p.id) AS postCount')
            ->addSelect('COALESCE(MAX(_p.createdAt), _t.createdAt) AS lastPostAt')
            ->innerJoin('_t.user', '_u')
            ->leftJoin('_t.posts', '_p')
            ->where('_t.forum = :forum')
            ->setParameter('forum', $forum)
            ->groupBy('_t.id')
            ->addGroupBy('_u.id')
            ->orderBy('lastPostAt', 'DESC')
            ->addOrderBy('_t.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $page = [];

        foreach ($rows as $row) {
            $page[] = [
                'topic' => $row[0],
                'postCount' => (int) $row['postCount'],
                'lastPostAt' => new DateTimeImmutable($row['lastPostAt']),
            ];
        }

        return $page;
    }

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

    public function findFirstByForumId(int $forumId): ?TopicEntity
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