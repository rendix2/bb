<?php

namespace App\Authorization\Scopes;

use App\Authorization\IAuthorizationScope;
use App\Authorization\Identity;
use App\Authorization\Scopes\User;
use App\Models\Users2ForumsManager;

/**
 * Description of Forum
 *
 * @author rendix2
 */
class Forum implements IAuthorizationScope
{

    const ROLE_MODERATOR = 'Forum:moderator';
    
    const ROLE_FORUM_VIEWER = 'Forum:viewer';
    const ROLE_FORUM_THANKER = 'Forum:thanker';
    const ROLE_FORUM_FAST_REPLIER = 'Forum:fastReplier';
    
    const ROLE_FORUM_POST_ADDER = 'Forum:postAdder';
    const ROLE_FORUM_POST_UPDATER = 'Forum:postUpdater';
    const ROLE_FORUM_POST_DELETER = 'Forum:postdeleter';
    
    const ROLE_FORUM_TOPIC_ADDER = 'Forum:topicAdder';
    const ROLE_FORUM_TOPIC_UPDATER = 'Forum:topicUpdater';
    const ROLE_FORUM_TOPIC_DELETER = 'Forum:topicDeleter';    
    
    const ACTION_VIEW       = [self::class, 'forum_view'];        
    const ACTION_THANK      = [self::class, 'forum_thank'];    
    const ACTION_FAST_REPLY = [self::class, 'forum_fast_reply'];
    
    const ACTION_POST_ADD     = [self::class, 'post_add'];
    const ACTION_POST_DELETE  = [self::class, 'post_delete'];
    const ACTION_POST_UPDATE  = [self::class, 'post_update'];
    
    const ACTION_TOPIC_ADD     = [self::class, 'topic_add'];
    const ACTION_TOPIC_UPDATE  = [self::class, 'topic_update'];
    const ACTION_TOPIC_DELETE  = [self::class, 'topic_delete'];
    
    /**
     *
     * @var User[] $moderators
     */
    private $moderators = [];
    
    private $forumEntity;
    
    /**
     *
     * @var \App\Models\Users2GroupsManager $users2GroupsManager
     */
    private $users2GroupsManager;
    
    private $userPermission;
    
    private $groupPermission;    
    
    private $users2ForumsManager;
    
    /**
     * 
     * @param int $id
     * @param User[] $moderators
     */
    public function __construct(\App\Models\Entity\Forum $forumEntity, $moderators, \App\Models\Users2GroupsManager $users2GroupsManager, Users2ForumsManager $users2ForumsManager)
    {
        $this->forumEntity         = $forumEntity;
        $this->moderators          = $moderators;
        $this->users2GroupsManager = $users2GroupsManager;
        $this->users2ForumsManager = $users2ForumsManager;
    }

    /**
     * @param Identity $identity
     *
     * @return array
     */
    public function getIdentityRoles(Identity $identity)
    {
        $roles = [self::ROLE_FORUM_VIEWER];
                
        //user
        if ($this->userPermission) {
            $this->userPermission = $userForum = $this->users2ForumsManager->getAllFull($identity->getId(), $this->forumEntity->getForum_id());
        } else {
            $userForum = $this->userPermission;
        }
       
       if ($userForum) {
            $userForum = $userForum[0];

            if ($userForum->post_add) {
                $roles[] = self::ROLE_FORUM_POST_ADDER;
            }

            if ($userForum->post_update) {
                $roles[] = self::ROLE_FORUM_POST_UPDATER;
            }

            if ($userForum->post_delete) {
                $roles[] = self::ROLE_FORUM_POST_DELETER;
            }

            if ($userForum->topic_add) {
                $roles[] = self::ROLE_FORUM_TOPIC_ADDER;
            }

            if ($userForum->topic_update) {
                $roles[] = self::ROLE_FORUM_TOPIC_UPDATER;
            }

            if ($userForum->topic_delete) {
                $roles[] = self::ROLE_FORUM_TOPIC_DELETER;
            }

            if ($userForum->topic_thank) {
                $roles[] = self::ROLE_FORUM_THANKER;
            }

            if ($userForum->topic_fast_reply) {
                $roles[] = self::ROLE_FORUM_FAST_REPLIER;
            }
        }

        // group   
        if ($this->groupPermission) {
            $this->groupPermission = $groupForums = $this->users2GroupsManager->getForumsPermissionsByUserThroughGroupAndForum($identity->getId(), $this->forumEntity->getForum_id());
        } else {
            $groupForums = $this->groupPermission;
        }

        if ($groupForums) {

            if ($groupForums->post_add && !in_array(self::ROLE_FORUM_POST_ADDER, $roles)) {
                $roles[] = self::ROLE_FORUM_POST_ADDER;
            }

            if ($groupForums->post_update && !in_array(self::ROLE_FORUM_POST_UPDATER, $roles)) {
                $roles[] = self::ROLE_FORUM_POST_UPDATER;
            }

            if ($groupForums->post_delete && !in_array(self::ROLE_FORUM_POST_DELETER, $roles)) {
                $roles[] = self::ROLE_FORUM_POST_DELETER;
            }

            if ($groupForums->topic_add && !in_array(self::ROLE_FORUM_TOPIC_ADDER, $roles)) {
                $roles[] = self::ROLE_FORUM_TOPIC_ADDER;
            }

            if ($groupForums->topic_update && !in_array(self::ROLE_FORUM_TOPIC_UPDATER, $roles)) {
                $roles[] = self::ROLE_FORUM_TOPIC_UPDATER;
            }

            if ($groupForums->topic_delete && !in_array(self::ROLE_FORUM_TOPIC_DELETER, $roles)) {
                $roles[] = self::ROLE_FORUM_TOPIC_DELETER;
            }

            if ($groupForums->topic_thank && !in_array(self::ROLE_FORUM_THANKER, $roles)) {
                $roles[] = self::ROLE_FORUM_THANKER;
            }

            if ($groupForums->topic_fast_reply && !in_array(self::ROLE_FORUM_FAST_REPLIER, $roles)) {
                $roles[] = self::ROLE_FORUM_FAST_REPLIER;
            }
        }

        // forum                
        if ($this->forumEntity->getForum_post_add() && !in_array(self::ROLE_FORUM_POST_ADDER, $roles)) {
            $roles[] = self::ROLE_FORUM_POST_ADDER;
        }
        
        if ($this->forumEntity->getForum_post_update() && !in_array(self::ROLE_FORUM_POST_UPDATER, $roles)) {
            $roles[] = self::ROLE_FORUM_POST_UPDATER;
        }
        
        if ($this->forumEntity->getForum_post_delete() && !in_array(self::ROLE_FORUM_POST_DELETER, $roles)) {
            $roles[] = self::ROLE_FORUM_POST_DELETER;
        }     
        
        if ($this->forumEntity->getForum_topic_add() && !in_array(self::ROLE_FORUM_TOPIC_ADDER, $roles)) {
            $roles[] = self::ROLE_FORUM_TOPIC_ADDER;
        }  
        
        if ($this->forumEntity->getForum_topic_update() && !in_array(self::ROLE_FORUM_TOPIC_UPDATER, $roles)) {
            $roles[] = self::ROLE_FORUM_TOPIC_UPDATER;
        }

        if ($this->forumEntity->getForum_topic_delete() && !in_array(self::ROLE_FORUM_TOPIC_DELETER, $roles)) {
            $roles[] = self::ROLE_FORUM_TOPIC_DELETER;
        }  
        
        if ($this->forumEntity->getForum_thank() && !in_array(self::ROLE_FORUM_THANKER, $roles)) {
            $roles[] = self::ROLE_FORUM_THANKER;
        }  
        
        if ($this->forumEntity->getForum_fast_reply()) {
            $roles[] = self::ROLE_FORUM_FAST_REPLIER;
        }
        
        foreach ($this->moderators as $moderator) {
            if ($moderator->getIdentity()->getId() === $identity->getId()) {
                $roles[] = self::ROLE_MODERATOR;
                break;
            }
        }
        
        return $roles;
    }
}
