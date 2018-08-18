<?php

namespace App\Authorization\Scopes;

/**
 * Description of Post
 *
 * @author rendix2
 */
class Post implements \App\Authorization\IAuthorizationScope {
    
    const ROLE_AUTHOR = 'Post:author';
    
    const ACTION_ADD    = [self::class, 'add'];
    const ACTION_EDIT   = [self::class, 'edit'];
    const ACTION_DELETE = [self::class, 'delete'];
    
    /**
     * @var int $id;
     */
    private $id;
    
    /**
     *
     * @var \App\Authorization\Scopes\Topic $topic
     */
    private $topic;
    
    /**
     *
     * @var \App\Authorization\Scopes\User $author
     */
    private $author;

    /**
     * @param \App\Authorization\Identity $identity
     *
     * @return array
     */
    public function getIdentityRoles(\App\Authorization\Identity $identity) {
        $roles = [];
        
        if ($this->author->getIdentity() === $identity) {
            $roles[] = self::ROLE_AUTHOR;
        }
        
        return array_merge($roles, $this->topic->getIdentityRoles($identity));
    }
}
