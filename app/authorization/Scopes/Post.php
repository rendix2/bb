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
    
    const ACTION_ADD    = [self::class, 'add'];
    const ACTION_EDIT   = [self::class, 'edit'];
    const ACTION_DELETE = [self::class, 'delete'];
    
    /**
     * @var int $id;
     */
    private $id;
    
    /**
     *
     * @var Topic $topic
     */
    private $topic;
    
    /**
     *
     * @var User $author
     */
    private $author;

    /**
     * @param Identity $identity
     *
     * @return array
     */
    public function getIdentityRoles(Identity $identity)
    {
        $roles = [];
        
        if ($this->author->getIdentity() === $identity) {
            $roles[] = self::ROLE_AUTHOR;
        }
        
        return array_merge($roles, $this->topic->getIdentityRoles($identity));
    }
}
