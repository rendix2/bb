<?php declare(strict_types=1);

namespace App\services;

use App\Authorization\Identity;
use App\Authorization\Scopes\ForumScope;
use App\Authorization\Scopes\PostScope;
use App\Authorization\Scopes\TopicScope;
use App\Authorization\Scopes\User;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Repository\GroupForumPermissionRepository;
use App\Model\Repository\ThankRepository;
use App\Model\Repository\UserForumPermissionRepository;

class ScopeService
{

    public function __construct(
        private readonly UserForumPermissionRepository $userForumPermissionRepository,
        private readonly GroupForumPermissionRepository $groupForumPermissionRepository,

        private readonly ThankRepository $thankRepository,
    )
    {
    }

    public function loadForum(ForumEntity $forum): ForumScope
    {
        $moderators = $forum->moderatorUsers;
        $moderatorsI = [];

        foreach ($moderators as $moderator) {
            $moderatorIdentity = new Identity($moderator->user_id, [ForumScope::ROLE_MODERATOR]);
            $moderatorUser     = new User($moderatorIdentity);

            $moderatorsI[] = $moderatorUser;
        }

        return new ForumScope($forum, $moderatorsI,  $this->userForumPermissionRepository, $this->groupForumPermissionRepository);
    }

    public function loadTopic(ForumEntity $forum, TopicEntity $topic): TopicScope
    {
        $topicIdentity = new Identity($topic->firstPost->user->id, [TopicScope::ROLE_AUTHOR]);
        $topicAuthor   = new User($topicIdentity);

        $thanks = $this->thankRepository->findByTopic($topic);

        return new TopicScope($topic, $topicAuthor, $this->loadForum($forum), $thanks);
    }

    public function loadPost(ForumEntity $forumEntity, TopicEntity $topicEntity, PostEntity $postEntity): PostScope
    {
        $postIdentity  = new Identity($postEntity->user->id, [PostScope::ROLE_AUTHOR]);

        return new PostScope($postEntity, $this->loadTopic($forumEntity, $topicEntity));
    }

}