<?php

namespace App\Authorization;

/**
 * Description of AuthorizationPresenter
 *
 * @author rendi
 */
trait AuthorizationPresenter
{
     protected function getLoggedInUser()
    {        
        $identity = new \App\Authorization\Identity($this->user->id, $this->user->roles);
        
        return new \App\Authorization\Scopes\User($identity);
    }
    
    protected function loadCategory($id)
    {
        return new \App\Authorization\Scopes\Category();
    }
    
    protected function loadForum(\App\Models\Entity\Forum $forum)
    {        
        $moderators = $this->moderators->getAllByRight($forum->getForum_id());
        $moderatorsI = [];
        
        foreach ($moderators as $moderator) {
            $moderatorIdentity = new \App\Authorization\Identity($moderator->user_id, \App\Authorization\Scopes\Forum::ROLE_MODERATOR);
            $moderatorUser     = new \App\Authorization\Scopes\User($moderatorIdentity);
            
            $moderatorsI[] = $moderatorUser;
        }
                
        return new \App\Authorization\Scopes\Forum($forum, $moderatorsI, $this->users2GroupsManager, $this->users2ForumsManager); 
    }    
    
    protected function loadTopic(\App\Models\Entity\Forum $forum, \App\Models\Entity\Topic $topic)
    {
        
        $topicIdentity = new \App\Authorization\Identity($topic->getTopic_first_user_id(), [\App\Authorization\Scopes\Topic::ROLE_AUTHOR]);        
        $topicAuthor   = new \App\Authorization\Scopes\User($topicIdentity);
        
        $thanks = $this->thanksManager->getAllByTopic($topic->getTopic_id());
        
        return new \App\Authorization\Scopes\Topic($topic, $topicAuthor, $this->loadForum($forum), $thanks);
    }    
    
    protected function loadPost(\App\Models\Entity\Forum $forumEntity, \App\Models\Entity\Topic $topicEntity, \App\Models\Entity\Post $postEntity)
    {
        $postIdentity  = new \App\Authorization\Identity($postEntity->getPost_user_id(), [\App\Authorization\Scopes\Post::ROLE_AUTHOR]);        
        $postAuthor    = new \App\Authorization\Scopes\User($postIdentity);
                        
        return new \App\Authorization\Scopes\Post($postEntity, $this->loadTopic($forumEntity, $topicEntity), $topicEntity);
    }
        
    protected function requireAccess(\App\Authorization\IAuthorizationScope $scope, array $action)
    {
        if (!$this->isAllowed($scope, $action)) {
            throw new \Exception();
        }
    }

    protected function isAllowed(\App\Authorization\IAuthorizationScope $scope, array $action)
    {
        return $this->authorizator->isAllowed($this->getLoggedInUser()->getIdentity(), $scope, $action);
    }
}
