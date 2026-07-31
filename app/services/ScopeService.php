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
use App\Model\Repository\ThankRepository;
use App\Models\ModeratorManager;
use App\Models\User2GroupManager;
use App\Models\Users2ForumsManager;

class ScopeService
{

    public function __construct(
        private readonly User2GroupManager $users2GroupsManager,
        private readonly Users2ForumsManager $users2ForumsManager,

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

        return new ForumScope($forum, $moderatorsI, $this->users2GroupsManager, $this->users2ForumsManager);
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

        return new PostScope($postEntity, $this->loadTopic($forumEntity, $topicEntity), $topicEntity);
    }

}