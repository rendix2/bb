<?php

namespace App\Authorization\Scopes;

use App\Authorization\Identity;

/**
 * Description of Topic
 *
 * @author rendix2
 */
class Topic implements \App\Authorization\IAuthorizationScope
{

    const ROLE_AUTHOR = 'Topic:author';
    
    const ROLE_THANKER= 'Topic:thanker';
    
    const ROLE_NOT_THANKER = 'Topic:notThnanker';
    
    const ROLE_DELETER = 'Topic:deleter';
    
    const ROLE_EDITOR = 'Topic:Editor';
    
    const ACTION_VIEW   = [self::class, 'view'];
    const ACTION_ADD    = [self::class, 'add'];
    const ACTION_EDIT   = [self::class, 'edit'];
    const ACTION_DELETE = [self::class, 'delete'];
    const ACTION_THANK  = [self::class, 'thank'];
    
    /**
     * @var int $id;
     */
    private $id;
    
    /**
     * @var Forum $forum
     */
    private $forumScope;
    
    /**
     * @var User $author
     */
    private $author;
    
    private $thanks;
    
    /**
     *
     * @var \App\Models\Entity\Topic $topic
     */
    private $topicEntity;

    /**
     * Topic constructor.
     *
     * @param \App\Models\Entity\Topic $topicEntity
     * @param User                     $author
     * @param Forum                    $forumScope
     * @param                          $thanks
     */
    public function __construct(\App\Models\Entity\Topic $topicEntity, User $author, Forum $forumScope, $thanks)
    {
        $this->topicEntity  = $topicEntity;
        $this->author = $author;
        $this->forumScope  = $forumScope;
        $this->thanks = $thanks;
    }

    /**
     * @param Identity $identity
     *
     * @return array
     */
    public function getIdentityRoles(Identity $identity)
    {
        if ($this->topicEntity->getTopic_locked()) {
            return $this->forumScope->getIdentityRoles($identity);
        }
        
        $roles = [];
        
        $isAuthor = $this->author->getIdentity()->getId() === $identity->getId();
                
        if ($isAuthor && in_array(Forum::ROLE_FORUM_TOPIC_ADDER, $this->forumScope->getIdentityRoles($identity), true)) {
            $roles[] = self::ROLE_AUTHOR;
        }
                
        if ($isAuthor && in_array(Forum::ROLE_FORUM_TOPIC_DELETER, $this->forumScope->getIdentityRoles($identity), true)) {
            $roles[] = self::ROLE_DELETER;
        }
        
        if ($isAuthor && in_array(Forum::ROLE_FORUM_TOPIC_UPDATER, $this->forumScope->getIdentityRoles($identity), true)) {
            $roles[] = self::ROLE_EDITOR;
        }
        
        $canThank = true;

        foreach ($this->thanks as $thank) {
            if ($thank->thank_user_id === $identity->getId()) {
                $canThank = false;
                break;
            }
        }
        
        if ($canThank) {
            $roles[] = self::ROLE_THANKER;
        } else {
            $roles[] = self::ROLE_NOT_THANKER;
        }
        
        return array_merge($this->forumScope->getIdentityRoles($identity), $roles);
    }
}
