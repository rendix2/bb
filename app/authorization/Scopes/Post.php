<?php

namespace App\Authorization\Scopes;

use App\Authorization\IAuthorizationScope;
use App\Authorization\Identity;

/**
 * Description of Post
 *
 * @author rendix2
 */
class Post implements IAuthorizationScope
{

    const ROLE_AUTHOR = 'Post:author';
    const ROLE_DELETER = 'Post:deleter';
    const ROLE_EDITOR = 'Post:editor';
    const ROLE_HISTORIER = 'Post:historier';
    
    const ACTION_VIEW    = [self::class, 'view'];
    const ACTION_ADD     = [self::class, 'add'];
    const ACTION_EDIT    = [self::class, 'edit'];
    const ACTION_DELETE  = [self::class, 'delete'];
    const ACTION_HISTORY = [self::class, 'history'];
    
    /**
     * @var int $id;
     */
    private $id;
    
    /**
     *
     * @var Topic $topic
     */
    private $topicScope;
    
    /**
     *
     * @var User $author
     */
    private $author;
    
    /**
     *
     * @var \App\Models\Entity\Post $post
     */
    private $post;
    
    /**
     *
     * @var \App\Models\Entity\Topic $topicEntity
     */
    private $topicEntity;

    /**
     *
     * @param \App\Models\Entity\Post  $post
     * @param Topic                    $topicScope
     * @param \App\Models\Entity\Topic $topicEntity
     */
    public function __construct(\App\Models\Entity\Post $post, \App\Authorization\Scopes\Topic $topicScope, \App\Models\Entity\Topic $topicEntity)
    {
        $this->post        = $post;
        $this->topicScope  = $topicScope;
        $this->topicEntity = $topicEntity;
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
        
        if ($isAuthor && in_array(Forum::ROLE_FORUM_POST_ADDER, $this->topicScope->getIdentityRoles($identity), true)) {
            $roles[] = self::ROLE_AUTHOR;
        }
        
        if ($isAuthor && in_array(Forum::ROLE_FORUM_POST_DELETER, $this->topicScope->getIdentityRoles($identity), true)) {
            $roles[] = self::ROLE_DELETER;
        }
        
        if ($isAuthor && in_array(Forum::ROLE_FORUM_POST_UPDATER, $this->topicScope->getIdentityRoles($identity), true)) {
            $roles[] = self::ROLE_EDITOR;
        }
                
        return array_merge($this->topicScope->getIdentityRoles($identity), $roles);
    }
}
