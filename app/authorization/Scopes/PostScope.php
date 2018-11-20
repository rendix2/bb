<?php

namespace App\Authorization\Scopes;

use App\Authorization\IAuthorizationScope;
use App\Authorization\Identity;
use App\Models\Entity\PostEntity;
use App\Models\Entity\TopicEntity;

/**
 * Description of Post
 *
 * @author rendix2
 * @package App\Authorization\Scopes
 */
class PostScope implements IAuthorizationScope
{

    const ROLE_AUTHOR    = 'Post:author';
    const ROLE_DELETER   = 'Post:deleter';
    const ROLE_EDITOR    = 'Post:editor';
    const ROLE_HISTORIER = 'Post:historier';
    
    const ACTION_VIEW    = [self::class, 'view'];
    const ACTION_ADD     = [self::class, 'add'];
    const ACTION_EDIT    = [self::class, 'edit'];
    const ACTION_DELETE  = [self::class, 'delete'];
    const ACTION_HISTORY = [self::class, 'history'];
    
    /**
     *
     * @var TopicEntity $topic
     */
    private $topicScope;
    
    /**
     *
     * @var PostEntity $post
     */
    private $post;
    
    /**
     *
     * @var TopicEntity $topicEntity
     */
    private $topicEntity;
    
    /**
     * 
     * @param User $author
     * @param TopicEntity $topicScope
     */
    public function __construct(PostEntity $post, TopicScope $topicScope, TopicEntity $topicEntity)
    {
        $this->post        = $post;
        $this->topicScope  = $topicScope;
        $this->topicEntity = $topicEntity;
    }
    
    /**
     * 
     */
    public function __destruct()
    {
        $this->post        = null;
        $this->topicEntity = null;
        $this->topicEntity = null;
    }

    /**
     * @param Identity $identity
     *
     * @return array
     */
    public function getIdentityRoles(Identity $identity)
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
        
        if ($isAuthor && in_array(ForumScope::ROLE_FORUM_POST_ADDER, $this->topicScope->getIdentityRoles($identity))) {
            $roles[] = self::ROLE_AUTHOR;
        }
        
        if ($isAuthor && in_array(ForumScope::ROLE_FORUM_POST_DELETER, $this->topicScope->getIdentityRoles($identity))) {
            $roles[] = self::ROLE_DELETER;
        }
        
        if ($isAuthor && in_array(ForumScope::ROLE_FORUM_POST_UPDATER, $this->topicScope->getIdentityRoles($identity))) {
            $roles[] = self::ROLE_EDITOR;
        }
                
        return array_merge($this->topicScope->getIdentityRoles($identity), $roles);
    }
}
