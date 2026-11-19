<?php declare(strict_types=1);

namespace App\Model\Security;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\ForumPermissions;
use App\Model\Entity\GroupForumPermissionEntity;
use App\Model\Entity\GroupModeratorEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use App\Model\Entity\UserForumPermissionEntity;
use App\Model\Entity\UserGroupMemberEntity;
use App\Model\Entity\UserModeratorEntity;
use Nette\Security\Permission;

/**
 * May the user (null = guest) do the privilege with the forum / topic / post? Computes his roles there FROM THE DATABASE
 * (global roles, permission flags of the forum, his groups and himself, author, moderators, lock of the topic) and asks
 * the fixed rules of ForumAclFactory. Nothing of it is kept in the session.
 */
class ForumPermissionService
{
    private Permission $acl;

    /** @var array<string, array{flags: ForumPermissions, moderator: bool}> per request: "userId:forumId" */
    private array $forumRoles = [];

    public function __construct(
        private readonly EntityManagerDecorator $em,
        ForumAclFactory $aclFactory,
    ) {
        $this->acl = $aclFactory->create();
    }

    public function isAllowed(?UserEntity $user, ForumEntity|TopicEntity|PostEntity $subject, string $privilege) : bool
    {
        [$resource, $roles] = match (true) {
            $subject instanceof ForumEntity => [ForumPrivilege::RESOURCE_FORUM, $this->rolesInForum($user, $subject)],
            $subject instanceof TopicEntity => [ForumPrivilege::RESOURCE_TOPIC, $this->rolesInTopic($user, $subject)],
            default => [ForumPrivilege::RESOURCE_POST, $this->rolesAtPost($user, $subject)],
        };

        foreach ($roles as $role) {
            if ($this->acl->isAllowed($role, $resource, $privilege)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function rolesInForum(?UserEntity $user, ForumEntity $forum) : array
    {
        $roles = $this->globalRoles($user);

        if ($user === null) {
            return $roles;
        }

        ['flags' => $flags, 'moderator' => $moderator] = $this->forumRoles($user, $forum);

        if ($moderator) {
            $roles[] = ForumPrivilege::ROLE_MODERATOR;
        }

        if ($flags->topicAdd) {
            $roles[] = ForumPrivilege::ROLE_TOPIC_ADDER;
        }

        return $roles;
    }

    /**
     * @return list<string>
     */
    private function rolesInTopic(?UserEntity $user, TopicEntity $topic) : array
    {
        $roles = $this->globalRoles($user);

        if ($user === null) {
            return $roles;
        }

        ['flags' => $flags, 'moderator' => $moderator] = $this->forumRoles($user, $topic->forum);
        $open = !$topic->isLocked;
        $author = $topic->user->id === $user->id;

        if ($moderator) {
            $roles[] = ForumPrivilege::ROLE_MODERATOR;
        }

        if ($flags->postAdd && $open) {
            $roles[] = ForumPrivilege::ROLE_REPLIER;
        }

        if ($flags->topicFastReply && $open) {
            $roles[] = ForumPrivilege::ROLE_FAST_REPLIER;
        }

        if ($flags->topicThank) {
            $roles[] = ForumPrivilege::ROLE_THANKER;
        }

        if ($author && $open && $flags->topicUpdate) {
            $roles[] = ForumPrivilege::ROLE_EDITOR;
        }

        if ($author && $open && $flags->topicDelete) {
            $roles[] = ForumPrivilege::ROLE_REMOVER;
        }

        return $roles;
    }

    /**
     * @return list<string>
     */
    private function rolesAtPost(?UserEntity $user, PostEntity $post) : array
    {
        $roles = $this->globalRoles($user);

        if ($user === null) {
            return $roles;
        }

        ['flags' => $flags, 'moderator' => $moderator] = $this->forumRoles($user, $post->forum);
        $open = !$post->topic->isLocked;
        $author = $post->user->id === $user->id;

        if ($moderator) {
            $roles[] = ForumPrivilege::ROLE_MODERATOR;
        }

        if ($author && $open && $flags->postUpdate) {
            $roles[] = ForumPrivilege::ROLE_EDITOR;
        }

        if ($author && $open && $flags->postDelete) {
            $roles[] = ForumPrivilege::ROLE_REMOVER;
        }

        return $roles;
    }

    /**
     * The roles of the user from the database (not from the session), only those the ACL knows; a guest is "guest".
     *
     * @return list<string>
     */
    private function globalRoles(?UserEntity $user) : array
    {
        if ($user === null) {
            return [ForumPrivilege::ROLE_GUEST];
        }

        $roles = [ForumPrivilege::ROLE_GUEST];

        foreach ($user->roles as $role) {
            if ($this->acl->hasRole($role->name)) {
                $roles[] = $role->name;
            }
        }

        return $roles;
    }

    /**
     * The permission flags of the user in the forum (defaults of the forum + grants of his groups + his own grants) and
     * whether he moderates it (himself or through a group).
     *
     * @return array{flags: ForumPermissions, moderator: bool}
     */
    private function forumRoles(UserEntity $user, ForumEntity $forum) : array
    {
        $key = $user->id . ':' . $forum->id;

        if (isset($this->forumRoles[$key])) {
            return $this->forumRoles[$key];
        }

        $groupIds = array_map(
            static fn (array $row) : int => (int) $row['groupId'],
            $this->em->getRepository(UserGroupMemberEntity::class)
                ->createQueryBuilder('_m')
                ->select('IDENTITY(_m.group) AS groupId')
                ->where('_m.user = :user')
                ->setParameter('user', $user)
                ->getQuery()
                ->getArrayResult(),
        );

        $flags = $forum->permissions;

        foreach ($this->em->getRepository(UserForumPermissionEntity::class)->findBy(['user' => $user, 'forum' => $forum]) as $grant) {
            $flags = $flags->union($grant->permissions);
        }

        if ($groupIds !== []) {
            foreach ($this->em->getRepository(GroupForumPermissionEntity::class)->findBy(['forum' => $forum, 'group' => $groupIds]) as $grant) {
                $flags = $flags->union($grant->permissions);
            }
        }

        $moderator = $this->em->getRepository(UserModeratorEntity::class)->count(['user' => $user, 'forum' => $forum]) > 0
            || ($groupIds !== [] && $this->em->getRepository(GroupModeratorEntity::class)->count(['forum' => $forum, 'group' => $groupIds]) > 0);

        return $this->forumRoles[$key] = ['flags' => $flags, 'moderator' => $moderator];
    }

}
