<?php

namespace App\Authorization\Scopes;

/**
 * Description of Topic
 *
 * @author rendi
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
     * @var \App\Authorization\Scopes\Forum $forum
     */
    private $forum;
    
    /**
     * @var \App\Authorization\Scopes\User $author 
     */
    private $author;
    
    public function __construct(User $author, \App\Authorization\Scopes\Forum $forum)
    {
        $this->author = $author;
        $this->forum  = $forum;
    }

    public function getIdentityRoles(\App\Authorization\Identity $identity)
    {
        $roles = [];
                
        if ($this->author->getIdentity() === $identity) {
            $roles[] = self::ROLE_AUTHOR;
        }
        
        \Tracy\Debugger::barDump(array_merge($roles, $this->forum->getIdentityRoles($identity)));
        
        return array_merge($roles, $this->forum->getIdentityRoles($identity));
    }
    

}
