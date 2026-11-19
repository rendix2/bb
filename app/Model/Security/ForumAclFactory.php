<?php declare(strict_types=1);

namespace App\Model\Security;

use Nette\Security\Permission;

/**
 * The fixed rules of the forum: WHO may do WHAT is decided here, WHICH roles a user has in one forum / topic / post is
 * computed by ForumPermissionService from the database (permission flags, author, moderators, roles of the user).
 */
class ForumAclFactory
{
    public function create() : Permission
    {
        $acl = new Permission();

        $acl->addRole(ForumPrivilege::ROLE_GUEST);
        $acl->addRole(ForumPrivilege::ROLE_NORMAL, ForumPrivilege::ROLE_GUEST);
        $acl->addRole(ForumPrivilege::ROLE_ADMIN, ForumPrivilege::ROLE_NORMAL);

        foreach ([
            ForumPrivilege::ROLE_MODERATOR,
            ForumPrivilege::ROLE_TOPIC_ADDER,
            ForumPrivilege::ROLE_REPLIER,
            ForumPrivilege::ROLE_FAST_REPLIER,
            ForumPrivilege::ROLE_THANKER,
            ForumPrivilege::ROLE_EDITOR,
            ForumPrivilege::ROLE_REMOVER,
        ] as $role) {
            $acl->addRole($role);
        }

        $acl->addResource(ForumPrivilege::RESOURCE_FORUM);
        $acl->addResource(ForumPrivilege::RESOURCE_TOPIC);
        $acl->addResource(ForumPrivilege::RESOURCE_POST);

        $all = [ForumPrivilege::RESOURCE_FORUM, ForumPrivilege::RESOURCE_TOPIC, ForumPrivilege::RESOURCE_POST];

        // everybody reads (no private forums, as before)
        $acl->allow(ForumPrivilege::ROLE_GUEST, $all, ForumPrivilege::VIEW);

        $acl->allow(ForumPrivilege::ROLE_TOPIC_ADDER, ForumPrivilege::RESOURCE_FORUM, ForumPrivilege::TOPIC_ADD);
        $acl->allow(ForumPrivilege::ROLE_REPLIER, ForumPrivilege::RESOURCE_TOPIC, ForumPrivilege::REPLY);
        $acl->allow(ForumPrivilege::ROLE_FAST_REPLIER, ForumPrivilege::RESOURCE_TOPIC, ForumPrivilege::FAST_REPLY);
        $acl->allow(ForumPrivilege::ROLE_THANKER, ForumPrivilege::RESOURCE_TOPIC, ForumPrivilege::THANK);
        $acl->allow(ForumPrivilege::ROLE_EDITOR, [ForumPrivilege::RESOURCE_TOPIC, ForumPrivilege::RESOURCE_POST], ForumPrivilege::EDIT);
        $acl->allow(ForumPrivilege::ROLE_REMOVER, [ForumPrivilege::RESOURCE_TOPIC, ForumPrivilege::RESOURCE_POST], ForumPrivilege::DELETE);

        // a moderator may do everything in his forum, the admin everywhere
        $acl->allow(ForumPrivilege::ROLE_MODERATOR, $all);
        $acl->allow(ForumPrivilege::ROLE_ADMIN, $all);

        return $acl;
    }
}
