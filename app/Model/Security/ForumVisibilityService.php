<?php declare(strict_types=1);

namespace App\Model\Security;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\UserEntity;

/**
 * The forums whose content the viewer (null = guest) may read: active forums of an active category (or without one) where
 * ForumPermissionService allows VIEW. Lists that gather content across forums (the activity of a user, the search) take only
 * these, so they never show what the forum pages themselves would not.
 */
class ForumVisibilityService
{
    /** @var array<string, list<int>> per request: viewer id (or "guest") => forum ids */
    private array $visible = [];

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly ForumPermissionService $permissions,
    ) {
    }

    /**
     * @return list<int>
     */
    public function visibleForumIds(?UserEntity $viewer) : array
    {
        $key = $viewer === null ? 'guest' : (string) $viewer->id;

        if (isset($this->visible[$key])) {
            return $this->visible[$key];
        }

        /** @var list<ForumEntity> $forums */
        $forums = $this->em->getRepository(ForumEntity::class)
            ->createQueryBuilder('_f')
            ->leftJoin('_f.category', '_c')
            ->where('_f.active = true')
            ->andWhere('_c.id IS NULL OR _c.active = true')
            ->getQuery()
            ->getResult();

        $ids = [];

        foreach ($forums as $forum) {
            if ($this->permissions->isAllowed($viewer, $forum, ForumPrivilege::VIEW)) {
                $ids[] = $forum->id;
            }
        }

        return $this->visible[$key] = $ids;
    }

}
