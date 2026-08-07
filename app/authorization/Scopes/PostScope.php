<?php

namespace App\Authorization\Scopes;

use App\Authorization\IAuthorizationScope;
use App\Authorization\Identity;

/**
 * Description of Post
 *
 * @author rendix2
 * @package App\Authorization\Scopes
 */
class PostScope implements IAuthorizationScope
{

    const string ROLE_AUTHOR    = 'Post:author';
    const string ROLE_DELETER   = 'Post:deleter';
    const string ROLE_EDITOR    = 'Post:editor';
    const string ROLE_HISTORIER = 'Post:historier';
    
    const array ACTION_VIEW    = [self::class, 'view'];
    const array ACTION_ADD     = [self::class, 'add'];
    const array ACTION_EDIT    = [self::class, 'edit'];
    const array ACTION_DELETE  = [self::class, 'delete'];
    const array ACTION_HISTORY = [self::class, 'history'];
    
    private TopicScope $topicScope;
    
    private \App\Model\Entity\PostEntity $post;
    public function __construct(\App\Model\Entity\PostEntity $post, TopicScope $topicScope)
    {
        $this->post        = $post;
        $this->topicScope  = $topicScope;
    }

    /**
     * @param Identity $identity
     *
     * @return array
     */
    public function getIdentityRoles(Identity $identity): array
    {
        if ($this->topicEntity->getTopic_locked()) {
            return $this->topicScope->getIdentityRoles($identity);
        }
        
        if ($this->post->getPost_locked()) {
            return $this->topicScope->getIdentityRoles($identity);
        }
        
        $roles = [];
        
        $isAuthor = $this->post->getPost_user_id() === $identity->getId();
        
        if ($isAuthor) {
            $roles[] = self::ROLE_HISTORIER;
        }
        
        if ($isAuthor && in_array(ForumScope::ROLE_FORUM_POST_ADDER, $this->topicScope->getIdentityRoles($identity), true)) {
            $roles[] = self::ROLE_AUTHOR;
        }
        
        if ($isAuthor && in_array(ForumScope::ROLE_FORUM_POST_DELETER, $this->topicScope->getIdentityRoles($identity), true)) {
            $roles[] = self::ROLE_DELETER;
        }
        
        if ($isAuthor && in_array(ForumScope::ROLE_FORUM_POST_UPDATER, $this->topicScope->getIdentityRoles($identity), true)) {
            $roles[] = self::ROLE_EDITOR;
        }
                
        return array_merge($this->topicScope->getIdentityRoles($identity), $roles);
    }
}
