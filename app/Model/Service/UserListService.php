<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\PostEntity;
use App\Model\Entity\UserEntity;
use App\Model\Security\ForumVisibilityService;
use Doctrine\ORM\QueryBuilder;

/**
 * The list of users (Web:User:List, only for logged-in viewers): active users, filtered by a part of the username (case-insensitive),
 * sorted by the username, the registration or the post count. The post count is the one of the profile: only posts in the forums
 * the viewer may read (ForumVisibilityService + active category of the topic), so the list and the profile never differ.
 */
class UserListService
{
    public const string SortUsername = 'username';

    public const string SortRegistered = 'registered';

    public const string SortPosts = 'posts';

    public const array Sorts = [self::SortUsername, self::SortRegistered, self::SortPosts];

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ForumVisibilityService $visibility,
    ) {
    }

    /**
     * @return array{count: int, items: list<array{user: UserEntity, posts: int}>}
     */
    public function page(?UserEntity $viewer, string $filter, string $sort, bool $descending, int $offset, int $limit) : array
    {
        $count = (int) $this->filtered($filter)
            ->select('COUNT(_u.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $forumIds = $this->visibility->visibleForumIds($viewer);
        $qb = $this->filtered($filter)->select('_u');

        if ($forumIds === []) {
            $qb->addSelect('0 AS postCount');
        } else {
            $posts = $this->em->getRepository(PostEntity::class)
                ->createQueryBuilder('_p')
                ->select('COUNT(_p.id)')
                ->innerJoin('_p.topic', '_pt')
                ->innerJoin('_pt.category', '_ptc')
                ->where('_p.user = _u')
                ->andWhere('_pt.forum IN (:forums)')
                ->andWhere('_ptc.active = true');
            $qb->addSelect('(' . $posts->getDQL() . ') AS postCount')
                ->setParameter('forums', $forumIds);
        }

        $direction = $descending ? 'DESC' : 'ASC';

        match ($sort) {
            self::SortRegistered => $qb->orderBy('_u.createdAt', $direction),
            self::SortPosts => $qb->orderBy('postCount', $direction),
            default => $qb->addSelect('LOWER(_u.username) AS HIDDEN sortName')->orderBy('sortName', $direction),
        };

        /** @var list<array{0: UserEntity, postCount: int|string}> $rows */
        $rows = $qb->addOrderBy('_u.id', $direction)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return [
            'count' => $count,
            'items' => array_map(
                static fn (array $row) : array => ['user' => $row[0], 'posts' => (int) $row['postCount']],
                $rows,
            ),
        ];
    }

    /**
     * Active users whose username contains the filter (case-insensitive; % and _ are literal).
     */
    private function filtered(string $filter) : QueryBuilder
    {
        $qb = $this->em->getRepository(UserEntity::class)
            ->createQueryBuilder('_u')
            ->where('_u.isActive = true');

        if ($filter !== '') {
            $qb->andWhere('LOWER(_u.username) LIKE :filter')
                ->setParameter('filter', '%' . addcslashes(mb_strtolower($filter), '%_\\') . '%');
        }

        return $qb;
    }
}
