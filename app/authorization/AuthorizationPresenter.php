<?php

namespace App\Authorization;

use App\Authorization\Scopes\CategoryScope;
use App\Authorization\Scopes\ForumScope as Forum2;
use App\Authorization\Scopes\PostScope as Post2;
use App\Authorization\Scopes\TopicScope as Topic2;
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
     protected function getLoggedInUser()
    {        
        $identity = new Identity($this->user->id, $this->user->roles);
        
        return new User($identity);
    }
    
    protected function loadCategory($id)
    {
        return new CategoryScope();
    }
    
    protected function loadForum(ForumEntity $forum)
    {        
        $moderators = $this->moderators->getAllByRight($forum->getForum_id());
        $moderatorsI = [];
        
        foreach ($moderators as $moderator) {
            $moderatorIdentity = new Identity($moderator->user_id, Forum2::ROLE_MODERATOR);
            $moderatorUser     = new User($moderatorIdentity);
            
            $moderatorsI[] = $moderatorUser;
        }
                
        return new Forum2($forum, $moderatorsI, $this->users2GroupsManager, $this->users2ForumsManager); 
    }    
    
    protected function loadTopic(ForumEntity $forum, TopicEntity $topic)
    {
        
        $topicIdentity = new Identity($topic->getTopic_first_user_id(), [Topic2::ROLE_AUTHOR]);        
        $topicAuthor   = new User($topicIdentity);
        
        $thanks = $this->thanksManager->getAllByTopic($topic->getTopic_id());
        
        return new Topic2($topic, $topicAuthor, $this->loadForum($forum), $thanks);
    }    
    
    protected function loadPost(ForumEntity $forumEntity, TopicEntity $topicEntity, PostEntity $postEntity)
    {
        $postIdentity  = new Identity($postEntity->getPost_user_id(), [Post2::ROLE_AUTHOR]);        
        $postAuthor    = new User($postIdentity);
                        
        return new Post2($postEntity, $this->loadTopic($forumEntity, $topicEntity), $topicEntity);
    }
        
    protected function requireAccess(IAuthorizationScope $scope, array $action)
    {
        if (!$this->isAllowed($scope, $action)) {
            throw new Exception();
        }
    }

    protected function isAllowed(IAuthorizationScope $scope, array $action)
    {
        return $this->authorizator->isAllowed($this->getLoggedInUser()->getIdentity(), $scope, $action);
    }
}
