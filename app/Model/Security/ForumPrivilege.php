<?php declare(strict_types=1);

namespace App\Model\Security;

/**
 * Resources and privileges of the forum ACL (ForumAclFactory) asked through ForumPermissionService.
 */
final class ForumPrivilege
{
    public const string RESOURCE_FORUM = 'forum';
    public const string RESOURCE_TOPIC = 'topic';
    public const string RESOURCE_POST = 'post';

    public const string VIEW = 'view';
    public const string TOPIC_ADD = 'topicAdd';
    public const string REPLY = 'reply';
    public const string FAST_REPLY = 'fastReply';
    public const string THANK = 'thank';
    public const string EDIT = 'edit';
    public const string DELETE = 'delete';
    public const string MODERATE = 'moderate';

    public const string ROLE_GUEST = 'guest';
    public const string ROLE_NORMAL = 'Normal';
    public const string ROLE_ADMIN = 'Admin';

    // contextual roles: given by ForumPermissionService for one forum / topic / post, computed from the database
    public const string ROLE_MODERATOR = 'moderator';
    public const string ROLE_TOPIC_ADDER = 'topicAdder';
    public const string ROLE_REPLIER = 'replier';
    public const string ROLE_FAST_REPLIER = 'fastReplier';
    public const string ROLE_THANKER = 'thanker';
    public const string ROLE_EDITOR = 'editor';
    public const string ROLE_REMOVER = 'remover';
}
