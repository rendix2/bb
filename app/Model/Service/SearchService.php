<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use App\Model\Security\ForumVisibilityService;
use Doctrine\ORM\QueryBuilder;

/**
 * The search of the forum (slice 4h, decision of 2026-11-19: PostgreSQL full-text): posts by their title and text, topics by
 * their name, the most relevant first (ts_rank, then the newest). Only from the forums the viewer may read
 * (ForumVisibilityService) and an active category of the topic, as every list across forums. The DQL functions TS_MATCH /
 * TS_RANK (App\Database\Query) write the same expression as the GIN indexes of post and topic.
 */
class SearchService
{
    /**
     * The document of a post: the same expression as the index K__Post__Search (postUp() of the migration of post).
     */
    public const string PostDocument = "CONCAT(COALESCE(_p.title, ''), ' ', _p.text)";

    /**
     * The document of a topic: the same expression as the index K__Topic__Search.
     */
    public const string TopicDocument = '_t.name';

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ForumVisibilityService $visibility,
    ) {
    }

    /**
     * @return array{count: int, items: list<PostEntity>}
     */
    public function posts(?UserEntity $viewer, string $query, int $offset, int $limit) : array
    {
        $forumIds = $this->visibility->visibleForumIds($viewer);

        if ($forumIds === [] || trim($query) === '') {
            return ['count' => 0, 'items' => []];
        }

        $count = (int) $this->matchingPosts($forumIds, $query)
            ->select('COUNT(_p.id)')
            ->getQuery()
            ->getSingleScalarResult();

        /** @var list<PostEntity> $items */
        $items = $this->matchingPosts($forumIds, $query)
            ->select('_p', '_t', '_u')
            ->innerJoin('_p.user', '_u')
            ->addSelect('TS_RANK(' . self::PostDocument . ', :query) AS HIDDEN relevance')
            ->orderBy('relevance', 'DESC')
            ->addOrderBy('_p.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return ['count' => $count, 'items' => $items];
    }

    /**
     * @return array{count: int, items: list<TopicEntity>}
     */
    public function topics(?UserEntity $viewer, string $query, int $offset, int $limit) : array
    {
        $forumIds = $this->visibility->visibleForumIds($viewer);

        if ($forumIds === [] || trim($query) === '') {
            return ['count' => 0, 'items' => []];
        }

        $count = (int) $this->matchingTopics($forumIds, $query)
            ->select('COUNT(_t.id)')
            ->getQuery()
            ->getSingleScalarResult();

        /** @var list<TopicEntity> $items */
        $items = $this->matchingTopics($forumIds, $query)
            ->select('_t', '_f', '_u')
            ->innerJoin('_t.forum', '_f')
            ->innerJoin('_t.user', '_u')
            ->addSelect('TS_RANK(' . self::TopicDocument . ', :query) AS HIDDEN relevance')
            ->orderBy('relevance', 'DESC')
            ->addOrderBy('_t.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return ['count' => $count, 'items' => $items];
    }

    /**
     * @param non-empty-list<int> $forumIds
     */
    private function matchingPosts(array $forumIds, string $query) : QueryBuilder
    {
        return $this->em->getRepository(PostEntity::class)
            ->createQueryBuilder('_p')
            ->innerJoin('_p.topic', '_t')
            ->innerJoin('_t.category', '_tc')
            ->where('TS_MATCH(' . self::PostDocument . ', :query) = true')
            ->andWhere('_t.forum IN (:forums)')
            ->andWhere('_tc.active = true')
            ->setParameter('query', $query)
            ->setParameter('forums', $forumIds);
    }

    /**
     * @param non-empty-list<int> $forumIds
     */
    private function matchingTopics(array $forumIds, string $query) : QueryBuilder
    {
        return $this->em->getRepository(TopicEntity::class)
            ->createQueryBuilder('_t')
            ->innerJoin('_t.category', '_tc')
            ->where('TS_MATCH(' . self::TopicDocument . ', :query) = true')
            ->andWhere('_t.forum IN (:forums)')
            ->andWhere('_tc.active = true')
            ->setParameter('query', $query)
            ->setParameter('forums', $forumIds);
    }
}
