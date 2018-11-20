<?php

namespace App\Authorization;

use App\Authorization\Scopes\CategoryScope;
use App\Authorization\Scopes\ForumScope;
use App\Authorization\Scopes\PostScope;
use App\Authorization\Scopes\TopicScope;
use App\Authorization\Scopes\User;
use App\Models\Entity\ForumEntity;
use App\Models\Entity\PostEntity;
use App\Models\Entity\TopicEntity;
use Exception;

/**
 * Description of AuthorizationPresenter
 *
 * @author rendix2
 * @package App\Authorization
 */
trait AuthorizationPresenter
{
    /**
     * @return Scopes\User
     */
    protected function getLoggedInUser()
    {
        $identity = new Identity($this->user->id, $this->user->roles);

        return new User($identity);
    }

    /**
     * @param $id
     *
     * @return Scopes\Category
     */
    protected function loadCategory($id)
    {
        return new CategoryScope();
    }

    /**
     * @param \App\Models\Entity\Forum $forum
     *
     * @return Scopes\Forum
     */
    protected function loadForum(\App\Models\Entity\Forum $forum)
    {
        $moderators  = $this->moderators->getAllByRight($forum->getForum_id());
        $moderatorsI = [];
        
        foreach ($moderators as $moderator) {
            $moderatorIdentity = new Identity($moderator->user_id, ForumScope::ROLE_MODERATOR);
            $moderatorUser     = new User($moderatorIdentity);
            
            $moderatorsI[] = $moderatorUser;
        }
                
        return new ForumScope($forum, $moderatorsI, $this->users2GroupsManager, $this->users2ForumsManager); 
    }

    /**
     * @param ForumEntity $forum
     * @param TopicEntity $topic
     *
     * @return TopicScope
     */
    protected function loadTopic(ForumEntity $forum, TopicEntity $topic)
    {
        
        $topicIdentity = new Identity($topic->getTopic_first_user_id(), [TopicScope::ROLE_AUTHOR]);        
        $topicAuthor   = new User($topicIdentity);
        
        $thanks = $this->thanksManager->getAllByTopic($topic->getTopic_id());
        
        return new TopicScope($topic, $topicAuthor, $this->loadForum($forum), $thanks);
    }    
    
    /**
     * 
     * @param ForumEntity $forumEntity
     * @param TopicEntity $topicEntity
     * @param PostEntity  $postEntity
     * 
     * @return PostScope
     */
    protected function loadPost(ForumEntity $forumEntity, TopicEntity $topicEntity, PostEntity $postEntity)
    {
        $postIdentity  = new Identity($postEntity->getPost_user_id(), [PostScope::ROLE_AUTHOR]);        
        $postAuthor    = new User($postIdentity);
                        
        return new PostScope($postEntity, $this->loadTopic($forumEntity, $topicEntity), $topicEntity);
    }

    /**
     * @param IAuthorizationScope $scope
     * @param array               $action
     * 
     * @throws \Exception
     */
    protected function requireAccess(IAuthorizationScope $scope, array $action)
    {
        if (!$this->isAllowed($scope, $action)) {
            throw new Exception();
        }
    }

    /**
     * @param IAuthorizationScope $scope
     * @param array               $action
     * @return mixed
     */
    protected function isAllowed(IAuthorizationScope $scope, array $action)
    {
        return $this->authorizator->isAllowed($this->getLoggedInUser()->getIdentity(), $scope, $action);
    }
}
