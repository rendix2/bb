<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\ForumPermissions;
use App\Model\Entity\GroupEntity;
use App\Model\Entity\GroupForumPermissionEntity;
use App\Model\Entity\GroupModeratorEntity;
use App\Model\Entity\UserEntity;
use App\Model\Entity\UserForumPermissionEntity;
use App\Model\Entity\UserGroupMemberEntity;
use App\Model\Entity\UserModeratorEntity;
use App\Model\Repository\UserRepository;
use DateTimeImmutable;
use Nette\Utils\Strings;

/**
 * The administration of groups and of the access to forums (slice 7b). The permissions of a user in a forum are the defaults
 * of the forum plus the grants of his groups (group_forum) and his own (user_forum), they only add (ForumPermissionService).
 * A grant without any flag adds nothing, so it is deleted. A group has no moderator of its own (user decision 2026-11-24),
 * a group or a user moderates forums (group_moderator, moderator_user).
 */
class ForumAccessAdminService
{
    public const string ERROR_GROUP_NAME = 'admin-access.error.groupName';
    public const string ERROR_GROUP_EXISTS = 'admin-access.error.groupExists';
    public const string ERROR_UNKNOWN_USER = 'admin-access.error.unknownUser';
    public const string ERROR_ALREADY_MEMBER = 'admin-access.error.alreadyMember';
    public const string ERROR_ALREADY_MODERATOR = 'admin-access.error.alreadyModerator';

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly UserRepository $users,
    ) {
    }

    public function saveGroup(?GroupEntity $group, string $name) : GroupEntity
    {
        $name = Strings::trim($name);

        if ($name === '' || Strings::length($name) > 255) {
            throw new AdminException(self::ERROR_GROUP_NAME);
        }

        $same = $this->em->getRepository(GroupEntity::class)
            ->createQueryBuilder('_g')
            ->select('COUNT(_g.id)')
            ->where('LOWER(_g.name) = :name')
            ->setParameter('name', mb_strtolower($name));

        if ($group !== null) {
            $same->andWhere('_g.id != :id')->setParameter('id', $group->id);
        }

        if ((int) $same->getQuery()->getSingleScalarResult() > 0) {
            throw new AdminException(self::ERROR_GROUP_EXISTS, ['name' => $name]);
        }

        if ($group === null) {
            $group = new GroupEntity();
            $this->em->persist($group);
        } else {
            $group->updatedAt = new DateTimeImmutable();
        }

        $group->name = $name;
        $this->em->flush();

        return $group;
    }

    /**
     * Deletes the group with its memberships, grants and moderation (the foreign keys cascade); the users stay.
     */
    public function deleteGroup(GroupEntity $group) : void
    {
        $this->em->remove($group);
        $this->em->flush();
    }

    /**
     * @return list<UserEntity>
     */
    public function members(GroupEntity $group) : array
    {
        /** @var list<UserEntity> */
        return $this->em->getRepository(UserEntity::class)
            ->createQueryBuilder('_u')
            ->innerJoin(UserGroupMemberEntity::class, '_m', 'WITH', '_m.user = _u')
            ->where('_m.group = :group')
            ->setParameter('group', $group)
            ->addSelect('LOWER(_u.username) AS HIDDEN sortName')
            ->orderBy('sortName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function addMember(GroupEntity $group, string $username) : UserEntity
    {
        $user = $this->user($username);

        if ($this->em->getRepository(UserGroupMemberEntity::class)->count(['group' => $group, 'user' => $user]) > 0) {
            throw new AdminException(self::ERROR_ALREADY_MEMBER, ['username' => $user->username]);
        }

        $member = new UserGroupMemberEntity();
        $member->group = $group;
        $member->user = $user;
        $this->em->persist($member);
        $this->em->flush();

        return $user;
    }

    public function removeMember(GroupEntity $group, UserEntity $user) : void
    {
        $this->em->createQueryBuilder()
            ->delete(UserGroupMemberEntity::class, '_m')
            ->where('_m.group = :group')
            ->andWhere('_m.user = :user')
            ->setParameter('group', $group)
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }

    /**
     * The grants and moderation of the group in every forum.
     *
     * @return array<int, array{permissions: ForumPermissions, moderator: bool}> forum id =>
     */
    public function groupAccess(GroupEntity $group) : array
    {
        $access = [];

        foreach ($this->em->getRepository(GroupForumPermissionEntity::class)->findBy(['group' => $group]) as $grant) {
            $access[$grant->forum->id] = ['permissions' => $grant->permissions, 'moderator' => false];
        }

        foreach ($this->em->getRepository(GroupModeratorEntity::class)->findBy(['group' => $group]) as $moderator) {
            $access[$moderator->forum->id] ??= ['permissions' => new ForumPermissions(), 'moderator' => false];
            $access[$moderator->forum->id]['moderator'] = true;
        }

        return $access;
    }

    /**
     * Sets the grant and the moderation of the group in one forum (an empty grant is deleted).
     */
    public function setGroupAccess(GroupEntity $group, ForumEntity $forum, ForumPermissions $permissions, bool $moderator) : void
    {
        $this->em->wrapInTransaction(function () use ($group, $forum, $permissions, $moderator) : void {
            $grant = $this->em->getRepository(GroupForumPermissionEntity::class)->findOneBy(['group' => $group, 'forum' => $forum]);

            if ($permissions->isEmpty()) {
                if ($grant !== null) {
                    $this->em->remove($grant);
                }
            } elseif ($grant === null) {
                $grant = new GroupForumPermissionEntity();
                $grant->group = $group;
                $grant->forum = $forum;
                $grant->permissions = $permissions;
                $this->em->persist($grant);
            } else {
                $grant->permissions = $permissions;
                $grant->updatedAt = new DateTimeImmutable();
            }

            $moderation = $this->em->getRepository(GroupModeratorEntity::class)->findOneBy(['group' => $group, 'forum' => $forum]);

            if ($moderator && $moderation === null) {
                $moderation = new GroupModeratorEntity();
                $moderation->group = $group;
                $moderation->forum = $forum;
                $this->em->persist($moderation);
            } elseif (!$moderator && $moderation !== null) {
                $this->em->remove($moderation);
            }

            $this->em->flush();
        });
    }

    /**
     * @return list<UserModeratorEntity>
     */
    public function userModerators(ForumEntity $forum) : array
    {
        /** @var list<UserModeratorEntity> */
        return $this->em->getRepository(UserModeratorEntity::class)
            ->createQueryBuilder('_m')
            ->select('_m', '_u')
            ->innerJoin('_m.user', '_u')
            ->where('_m.forum = :forum')
            ->setParameter('forum', $forum)
            ->addSelect('LOWER(_u.username) AS HIDDEN sortName')
            ->orderBy('sortName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function addUserModerator(ForumEntity $forum, string $username) : UserEntity
    {
        $user = $this->user($username);

        if ($this->em->getRepository(UserModeratorEntity::class)->count(['forum' => $forum, 'user' => $user]) > 0) {
            throw new AdminException(self::ERROR_ALREADY_MODERATOR, ['username' => $user->username]);
        }

        $moderator = new UserModeratorEntity();
        $moderator->forum = $forum;
        $moderator->user = $user;
        $this->em->persist($moderator);
        $this->em->flush();

        return $user;
    }

    public function removeUserModerator(UserModeratorEntity $moderator) : void
    {
        $this->em->remove($moderator);
        $this->em->flush();
    }

    /**
     * @return list<UserForumPermissionEntity>
     */
    public function userGrants(ForumEntity $forum) : array
    {
        /** @var list<UserForumPermissionEntity> */
        return $this->em->getRepository(UserForumPermissionEntity::class)
            ->createQueryBuilder('_g')
            ->select('_g', '_u')
            ->innerJoin('_g.user', '_u')
            ->where('_g.forum = :forum')
            ->setParameter('forum', $forum)
            ->addSelect('LOWER(_u.username) AS HIDDEN sortName')
            ->orderBy('sortName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Sets the own grant of the user in the forum (an empty one is deleted).
     */
    public function setUserGrant(ForumEntity $forum, string $username, ForumPermissions $permissions) : UserEntity
    {
        $user = $this->user($username);
        $grant = $this->em->getRepository(UserForumPermissionEntity::class)->findOneBy(['forum' => $forum, 'user' => $user]);

        if ($permissions->isEmpty()) {
            if ($grant !== null) {
                $this->em->remove($grant);
            }
        } elseif ($grant === null) {
            $grant = new UserForumPermissionEntity();
            $grant->forum = $forum;
            $grant->user = $user;
            $grant->permissions = $permissions;
            $this->em->persist($grant);
        } else {
            $grant->permissions = $permissions;
            $grant->updatedAt = new DateTimeImmutable();
        }

        $this->em->flush();

        return $user;
    }

    private function user(string $username) : UserEntity
    {
        return $this->users->findOneByUsername(Strings::trim($username))
            ?? throw new AdminException(self::ERROR_UNKNOWN_USER, ['username' => Strings::trim($username)]);
    }
}
