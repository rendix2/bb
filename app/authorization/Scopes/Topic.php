<?php

namespace App\Authorization\Scopes;

use App\Authorization\Identity;
use Tracy\Debugger;

/**
 * Description of Topic
 *
 * @author rendix2
 */
class Topic implements \App\Authorization\IAuthorizationScope
{

    const ROLE_AUTHOR = 'Topic:author';
    
    const ACTION_ADD    = [self::class, 'add'];
    const ACTION_EDIT   = [self::class, 'edit'];
    const ACTION_DELETE = [self::class, 'delete'];
    
    /**
     * @var int $id;
     */
    private $id;
    
    /**
     * @var Forum $forum
     */
    private $forum;
    
    /**
     * @var User $author
     */
    private $author;

    /**
     * Topic constructor.
     *
     * @param User  $author
     * @param Forum $forum
     */
    public function __construct(User $author, Forum $forum)
    {
        $this->author = $author;
        $this->forum  = $forum;
    }

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
        
        Debugger::barDump(array_merge($roles, $this->forum->getIdentityRoles($identity)));
        
        return array_merge($roles, $this->forum->getIdentityRoles($identity));
    }
}
