<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\RankEntity;
use App\Model\Entity\UserEntity;

/**
 * The rank shown at a user: his special rank (given by the admin), else the rank of his post count.
 */
class UserRankService
{
    /**
     * Ranks by the post count, the highest minimum first (loaded once per request).
     *
     * @var list<RankEntity>|null
     */
    private ?array $ranksByPosts = null;

    public function __construct(
        private readonly EntityManagerDecorator $em,
    ) {
    }

    public function rankOf(UserEntity $user, int $postCount) : ?RankEntity
    {
        if ($user->rank !== null) {
            return $user->rank;
        }

        foreach ($this->ranksByPosts() as $rank) {
            if ($postCount >= (int) $rank->minPosts) {
                return $rank;
            }
        }

        return null;
    }

    /**
     * @return list<RankEntity>
     */
    private function ranksByPosts() : array
    {
        if ($this->ranksByPosts === null) {
            $this->ranksByPosts = $this->em
                ->getRepository(RankEntity::class)
                ->createQueryBuilder('_r')
                ->where('_r.minPosts IS NOT NULL')
                ->orderBy('_r.minPosts', 'DESC')
                ->getQuery()
                ->getResult();
        }

        return $this->ranksByPosts;
    }

}
