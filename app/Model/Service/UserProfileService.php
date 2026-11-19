<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\GroupModeratorEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\ThankEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use App\Model\Entity\UserGroupMemberEntity;
use App\Model\Entity\UserModeratorEntity;
use App\Model\Security\ForumVisibilityService;
use Doctrine\ORM\QueryBuilder;

/**
 * What the profile of a user shows about his forum life. Everything is counted / listed only from the forums the VIEWER may
 * read (ForumVisibilityService), so the profile never reveals content of a hidden forum (the old profile did).
 */
class UserProfileService
{
    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ForumVisibilityService $visibility,
    ) {
    }

    /**
     * @return array{posts: int, topics: int, thanks: int} his posts, his topics and the thanks he received (for his posts
     *     and for his topics)
     */
    public function counts(UserEntity $user, ?UserEntity $viewer) : array
    {
        $forumIds = $this->visibility->visibleForumIds($viewer);

        if ($forumIds === []) {
            return ['posts' => 0, 'topics' => 0, 'thanks' => 0];
        }

        $posts = $this->visiblePosts($user, $forumIds)
            ->select('COUNT(_p.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $topics = $this->visibleTopics($user, $forumIds)
            ->select('COUNT(_t.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $thanks = $this->em->getRepository(ThankEntity::class)
            ->createQueryBuilder('_th')
            ->select('COUNT(_th.id)')
            ->innerJoin('_th.topic', '_t')
            ->innerJoin('_t.category', '_tc')
            ->leftJoin('_th.post', '_p')
            ->where('(_p.id IS NOT NULL AND _p.user = :user) OR (_p.id IS NULL AND _t.user = :user)')
            ->andWhere('_t.forum IN (:forums)')
            ->andWhere('_tc.active = true')
            ->setParameter('user', $user)
            ->setParameter('forums', $forumIds)
            ->getQuery()
            ->getSingleScalarResult();

        return ['posts' => (int) $posts, 'topics' => (int) $topics, 'thanks' => (int) $thanks];
    }

    /**
     * The forums he moderates (himself or through one of his groups), only those the viewer may read, by name.
     *
     * @return list<ForumEntity>
     */
    public function moderatedForums(UserEntity $user, ?UserEntity $viewer) : array
    {
        $forumIds = $this->visibility->visibleForumIds($viewer);

        if ($forumIds === []) {
            return [];
        }

        $moderated = array_map(
            static fn (array $row) : int => (int) $row['forumId'],
            $this->em->getRepository(UserModeratorEntity::class)
                ->createQueryBuilder('_m')
                ->select('IDENTITY(_m.forum) AS forumId')
                ->where('_m.user = :user')
                ->setParameter('user', $user)
                ->getQuery()
                ->getArrayResult(),
        );

        $byGroups = $this->em->getRepository(GroupModeratorEntity::class)
            ->createQueryBuilder('_gm')
            ->select('IDENTITY(_gm.forum) AS forumId')
            ->innerJoin(UserGroupMemberEntity::class, '_member', 'WITH', '_member.group = _gm.group')
            ->where('_member.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getArrayResult();

        foreach ($byGroups as $row) {
            $moderated[] = (int) $row['forumId'];
        }

        $ids = array_values(array_intersect(array_unique($moderated), $forumIds));

        if ($ids === []) {
            return [];
        }

        /** @var list<ForumEntity> $forums */
        $forums = $this->em->getRepository(ForumEntity::class)
            ->createQueryBuilder('_f')
            ->where('_f.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('_f.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $forums;
    }

    /**
     * His posts in the forums of $forumIds whose topic is in an active category (as Forum:Topic:View requires).
     *
     * @param list<int> $forumIds
     */
    private function visiblePosts(UserEntity $user, array $forumIds) : QueryBuilder
    {
        return $this->em->getRepository(PostEntity::class)
            ->createQueryBuilder('_p')
            ->innerJoin('_p.topic', '_t')
            ->innerJoin('_t.category', '_tc')
            ->where('_p.user = :user')
            ->andWhere('_t.forum IN (:forums)')
            ->andWhere('_tc.active = true')
            ->setParameter('user', $user)
            ->setParameter('forums', $forumIds);
    }

    /**
     * @param list<int> $forumIds
     */
    private function visibleTopics(UserEntity $user, array $forumIds) : QueryBuilder
    {
        return $this->em->getRepository(TopicEntity::class)
            ->createQueryBuilder('_t')
            ->innerJoin('_t.category', '_tc')
            ->where('_t.user = :user')
            ->andWhere('_t.forum IN (:forums)')
            ->andWhere('_tc.active = true')
            ->setParameter('user', $user)
            ->setParameter('forums', $forumIds);
    }

}
