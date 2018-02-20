<?php

namespace App;

use App\Models\UsersManager;
use Nette\Security\Permission;
use Nette\Security\User;

/**
 * Description of Authorizator
 *
 * @author rendi
 */
class Authorizator {

    private $acl;
    private $forumManager;
    private $user;
    private $userManager;

    public function __construct(Models\ForumsManager $forumsManager, User $user, UsersManager $userManager) {
        $this->acl          = new Permission();
        $this->forumManager = $forumsManager;
        $this->user         = $user;
        $this->userManager  = $userManager;

        $this->defineRoles();
        $this->defineResources();
        $this->definePrivilegies();
    }

    public function getAcl() {
        return $this->acl;
    }

    private function defineRoles() {
        $this->acl->addRole('guest');
        $this->acl->addRole('registered', 'guest');
        $this->acl->addRole('moderator', 'registered');
        $this->acl->addRole('juniorAdmin', 'moderator');
        $this->acl->addRole('admin', 'juniorAdmin');
    }

    private function defineResources() {
        foreach ($this->forumManager->getAllCached() as $forum) {
            $this->acl->addResource("" . $forum->forum_id);

            $this->acl->allow('guest', "" . $forum->forum_id, 'forum_view');

            if ($forum->forum_thank) {
                $this->acl->allow('registered', "" . $forum->forum_id, 'topic_thank');
            }

            if ($forum->forum_post_add) {
                $this->acl->allow('registered', "" . $forum->forum_id, 'post_add');
            }

            if ($forum->forum_post_delete) {
                $this->acl->allow('registered', "" . $forum->forum_id, 'post_delete');
            }

            if ($forum->forum_post_update) {
                $this->acl->allow('registered', "" . $forum->forum_id, 'post_update');
            }

            if ($forum->forum_topic_add) {
                $this->acl->allow('registered', "" . $forum->forum_id, 'topic_add');
            }

            if ($forum->forum_topic_delete) {
                $this->acl->allow('registered', "" . $forum->forum_id, 'topic_delete');
            }
            
            if ($forum->forum_fast_reply) {
                $this->acl->allow('registered', "" . $forum->forum_id, 'fast_reply');
            }            
        }

        foreach ($this->userManager->getForumsPermissionsByUserThroughGroup($this->user->getId()) as $perm) {
            if ($perm->topic_thank) {
                $this->acl->allow('registered', "" . $perm->forum_id, 'topic_thank');
            } else {
                $this->acl->deny('registered', "" . $perm->forum_id, 'topic_thank');
            }

            if ($perm->post_add) {
                $this->acl->allow('registered', "" . $perm->forum_id, 'post_add');
            } else {
                $this->acl->deny('registered', "" . $perm->forum_id, 'post_add');
            }

            if ($perm->post_delete) {
                $this->acl->allow('registered', "" . $perm->forum_id, 'post_delete');
            } else {
                $this->acl->deny('registered', "" . $perm->forum_id, 'post_delete');
            }

            if ($perm->post_edit) {
                $this->acl->allow('registered', "" . $perm->forum_id, 'post_update');
            } else {
                $this->acl->deny('registered', "" . $perm->forum_id, 'post_update');
            }

            if ($perm->topic_add) {
                $this->acl->allow('registered', "" . $perm->forum_id, 'topic_add');
            } else {
                $this->acl->deny('registered', "" . $perm->forum_id, 'topic_add');
            }

            if ($perm->topic_delete) {
                $this->acl->allow('registered', "" . $perm->forum_id, 'topic_delete');
            } else {
                $this->acl->deny('registered', "" . $perm->forum_id, 'topic_delete');
            }
        }

        if ($this->user->isInRole('admin')) {
            foreach ($this->forumManager->getAllCached() as $forum) {
                $this->acl->allow('admin', "" . $forum->forum_id, Permission::ALL);
            }
        }       
    }

    private function definePrivilegies() {

        $this->acl->deny('guest', Permission::ALL, 'topic_thank');
        $this->acl->deny('guest', Permission::ALL, 'post_add');
        $this->acl->deny('guest', Permission::ALL, 'post_delete');

        // moderator
        $this->acl->allow('moderator', Permission::ALL, 'post_delete');
        $this->acl->allow('moderator', Permission::ALL, 'post_update');
        $this->acl->allow('moderator', Permission::ALL, 'topic_thank');
    }

}
