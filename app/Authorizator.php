<?php

namespace App;

use App\Models\ModeratorsManager;
use App\Models\UsersManager;
use Nette\Security\Permission;
use Nette\Security\User;

/**
 * Description of Authorizator
 *
 * @author rendi
 */
class Authorizator
{
    /**
     * @var Permission $acl
     */
    private $acl;

    /**
     * @var Models\ForumsManager $forumManager
     */
    private $forumManager;

    /**
     * @var User $user
     */
    private $user;

    /**
     * @var UsersManager $userManager
     */
    private $userManager;
    
    const ROLES = [ 1 => 'guest', 2 => 'registered', 3 => 'moderator', 4 => 'juniorAdmin', 5 => 'Admin'];


    /**
     *
     * @var ModeratorsManager $moderatorsManager
     */
    private $moderatorsManager;

    /**
     * Authorizator constructor.
     *
     * @param Models\ForumsManager $forumsManager
     * @param User                 $user
     * @param UsersManager         $userManager
     */
    public function __construct(Models\ForumsManager $forumsManager, User $user, UsersManager $userManager, ModeratorsManager $moderatorsManager)
    {
        $this->acl               = new Permission();
        $this->forumManager      = $forumsManager;
        $this->user              = $user;
        $this->userManager       = $userManager;
        $this->moderatorsManager = $moderatorsManager;
    
        $this->defineRoles();       
        $this->defineResources();
        $this->definePrivilegies();   
    }

    /**
     * @return Permission
     */
    public function getAcl()
    {
        return $this->acl;
    }

    /**
     *
     */
    private function definePrivilegies()
    {
        $this->acl->deny('guest', Permission::ALL, 'topic_thank');
        $this->acl->deny('guest', Permission::ALL, 'post_add');
        $this->acl->deny('guest', Permission::ALL, 'post_delete');

        // moderator
        //$this->acl->allow('moderator', Permission::ALL, 'post_delete');
        //$this->acl->allow('moderator', Permission::ALL, 'post_update');
        //$this->acl->allow('moderator', Permission::ALL, 'topic_thank');
    }

    /**
     *
     */
    private function defineResources()
    {
        
        // adds all resources
        foreach ($this->forumManager->getAllCached() as $forum) {
            $this->acl->addResource('' . $forum->forum_id);
        }
        
        foreach ($this->forumManager->getAllCached() as $forum) {
                $this->acl->deny('guest', '' . $forum->forum_id, Permission::ALL);
            $this->acl->allow('guest', "" . $forum->forum_id, 'forum_view');
        }
        
        if ($this->user->isInRole('admin')) {
            foreach ($this->forumManager->getAllCached() as $forum) {
                $this->acl->allow('admin', "" . $forum->forum_id);
            }
        }
                
        if ($this->user->isInRole('moderator')) {
            $moderators = $this->moderatorsManager->getByLeftPairs($this->user->getId());
        
            foreach ($this->forumManager->getAllCached() as $forum) {
                if (in_array($forum->forum_id, $moderators, true)) {
                    $this->acl->allow('moderator', '' . $forum->forum_id, Permission::ALL);
                } else {
                    $this->acl->deny('moderator', '' . $forum->forum_id);
                }
            }
        }
        

        foreach ($this->forumManager->getAllCached() as $forum) {
            if ($forum->forum_thank) {
                $this->acl->allow('registered', '' . $forum->forum_id, 'topic_thank');
            }

            if ($forum->forum_post_add) {
                $this->acl->allow('registered', '' . $forum->forum_id, 'post_add');
            }

            if ($forum->forum_post_delete) {
                $this->acl->allow('registered', '' . $forum->forum_id, 'post_delete');
            }

            if ($forum->forum_post_update) {
                $this->acl->allow('registered', '' . $forum->forum_id, 'post_update');
            }

            if ($forum->forum_topic_add) {
                $this->acl->allow('registered', '' . $forum->forum_id, 'topic_add');
            }

            if ($forum->forum_topic_delete) {
                $this->acl->allow('registered', '' . $forum->forum_id, 'topic_delete');
            }

            if ($forum->forum_fast_reply) {
                $this->acl->allow('registered', '' . $forum->forum_id, 'fast_reply');
            }
        }
             
        foreach ($this->userManager->getForumsPermissionsByUserThroughGroup($this->user->getId()) as $perm) {
            if ($perm->topic_thank) {
                $this->acl->allow('registered', '' . $perm->forum_id, 'topic_thank');
            } else {
                $this->acl->deny('registered', '' . $perm->forum_id, 'topic_thank');
            }

            if ($perm->post_add) {
                $this->acl->allow('registered', '' . $perm->forum_id, 'post_add');
            } else {
                $this->acl->deny('registered', '' . $perm->forum_id, 'post_add');
            }

            if ($perm->post_delete) {
                $this->acl->allow('registered', '' . $perm->forum_id, 'post_delete');
            } else {
                $this->acl->deny('registered', '' . $perm->forum_id, 'post_delete');
            }

            if ($perm->post_edit) {
                $this->acl->allow('registered', '' . $perm->forum_id, 'post_update');
            } else {
                $this->acl->deny('registered', '' . $perm->forum_id, 'post_update');
            }

            if ($perm->topic_add) {
                $this->acl->allow('registered', '' . $perm->forum_id, 'topic_add');
            } else {
                $this->acl->deny('registered', '' . $perm->forum_id, 'topic_add');
            }

            if ($perm->topic_delete) {
                $this->acl->allow('registered', '' . $perm->forum_id, 'topic_delete');
            } else {
                $this->acl->deny('registered', '' . $perm->forum_id, 'topic_delete');
            }
        }
    }

    /**
     *
     */
    private function defineRoles()
    {
        $this->acl->addRole('guest');
        $this->acl->addRole('registered', 'guest');
        $this->acl->addRole('moderator', 'registered');
        $this->acl->addRole('juniorAdmin', 'moderator');
        $this->acl->addRole('admin', 'juniorAdmin');
    }
}
