<?php

namespace App\Authorization;

use Nette\Security\Permission;

/**
 * Description of PermissionFactory
 *
 * @author rendix2
 */
class PermissionFactory
{
    /**
     * @return Permission
     */
    public function create()
    {
        $permission = new Permission();
        
        $permission->addResource(Scopes\Category::class);
        $permission->addResource(Scopes\Forum::class);
        $permission->addResource(Scopes\Topic::class);
        $permission->addResource(Scopes\Post::class);
        
        $permission->addRole(Scopes\Topic::ROLE_THANKER); // can thank
        $permission->addRole(Scopes\Topic::ROLE_NOT_THANKER); // cant thank
        
        $permission->addRole(Identity::ROLE_HOST);
        $permission->addRole(Identity::ROLE_REGISTERED, [Identity::ROLE_HOST, Scopes\Topic::ROLE_NOT_THANKER]);
                
        $permission->addRole(Scopes\Forum::ROLE_FORUM_VIEWER, [Identity::ROLE_REGISTERED]);
        $permission->addRole(Scopes\Forum::ROLE_FORUM_THANKER, [Identity::ROLE_REGISTERED]);
        $permission->addRole(Scopes\Forum::ROLE_FORUM_FAST_REPLIER, [Identity::ROLE_REGISTERED]);
        
        $permission->addRole(Scopes\Forum::ROLE_FORUM_POST_ADDER, [Identity::ROLE_REGISTERED]);
        $permission->addRole(Scopes\Forum::ROLE_FORUM_POST_UPDATER, [Identity::ROLE_REGISTERED]);
        $permission->addRole(Scopes\Forum::ROLE_FORUM_POST_DELETER, [Identity::ROLE_REGISTERED]);
        
        $permission->addRole(Scopes\Forum::ROLE_FORUM_TOPIC_ADDER, [Identity::ROLE_REGISTERED]);
        $permission->addRole(Scopes\Forum::ROLE_FORUM_TOPIC_UPDATER, [Identity::ROLE_REGISTERED]);
        $permission->addRole(Scopes\Forum::ROLE_FORUM_TOPIC_DELETER, [Identity::ROLE_REGISTERED]);
        
        $permission->addRole(Scopes\Post::ROLE_AUTHOR, [Identity::ROLE_REGISTERED]);
        $permission->addRole(Scopes\Topic::ROLE_AUTHOR, [Identity::ROLE_REGISTERED]);
        
        $permission->addRole(Scopes\Post::ROLE_EDITOR, [Identity::ROLE_REGISTERED]);
        $permission->addRole(Scopes\Topic::ROLE_EDITOR, [Identity::ROLE_REGISTERED]);
        
        $permission->addRole(Scopes\Post::ROLE_DELETER, [Identity::ROLE_REGISTERED]);
        $permission->addRole(Scopes\Topic::ROLE_DELETER, [Identity::ROLE_REGISTERED]);
        
        $permission->addRole(Scopes\Post::ROLE_HISTORIER);
        
        $permission->addRole(Scopes\Forum::ROLE_MODERATOR, [Scopes\Post::ROLE_AUTHOR, Scopes\Post::ROLE_DELETER, Scopes\Post::ROLE_EDITOR, Scopes\Topic::ROLE_AUTHOR, Scopes\Topic::ROLE_DELETER, Scopes\Topic::ROLE_EDITOR]);
        
        $permission->addRole(Identity::ROLE_ADMIN, [Scopes\Forum::ROLE_MODERATOR]);
        
        $this->deny($permission, Scopes\Topic::ROLE_NOT_THANKER, Scopes\Topic::ACTION_THANK);
        //$this->deny($permission, Identity::ROLE_REGISTERED, Scopes\Post::ACTION_DELETE);
        //$this->deny($permission, Identity::ROLE_REGISTERED, Scopes\Topic::ACTION_DELETE);
        
        
        $this->allow($permission, Identity::ROLE_HOST, Scopes\Post::ACTION_VIEW);
        $this->allow($permission, Identity::ROLE_HOST, Scopes\Topic::ACTION_VIEW);
        $this->allow($permission, Identity::ROLE_HOST, Scopes\Forum::ACTION_VIEW);
               
        $this->allow($permission, Scopes\Forum::ROLE_FORUM_POST_ADDER, Scopes\Forum::ACTION_POST_ADD);
        $this->allow($permission, Scopes\Forum::ROLE_FORUM_TOPIC_ADDER, Scopes\Forum::ACTION_TOPIC_ADD);
        
        //$this->allow($permission, Scopes\Forum::ROLE_FORUM_POST_UPDATER, Scopes\Forum::ACTION_POST_UPDATE);
        //$this->allow($permission, Scopes\Forum::ROLE_FORUM_TOPIC_UPDATER, Scopes\Forum::ACTION_TOPIC_UPDATE);
        
        $this->allow($permission, Scopes\Forum::ROLE_FORUM_POST_DELETER, Scopes\Forum::ACTION_POST_DELETE);
        $this->allow($permission, Scopes\Forum::ROLE_FORUM_TOPIC_DELETER, Scopes\Forum::ACTION_TOPIC_DELETE);
        
        $this->allow($permission, Scopes\Forum::ROLE_FORUM_THANKER, Scopes\Forum::ACTION_THANK);
        $this->allow($permission, Scopes\Topic::ROLE_THANKER, Scopes\Topic::ACTION_THANK);
        
        $this->allow($permission, Scopes\Post::ROLE_EDITOR, Scopes\Post::ACTION_EDIT);
        $this->allow($permission, Scopes\Topic::ROLE_EDITOR, Scopes\Topic::ACTION_EDIT);
                
        $this->allow($permission, Scopes\Post::ROLE_DELETER, Scopes\Post::ACTION_DELETE);
        $this->allow($permission, Scopes\Topic::ROLE_DELETER, Scopes\Topic::ACTION_DELETE);
        
        $this->allow($permission, Scopes\Post::ROLE_HISTORIER, Scopes\Post::ACTION_HISTORY);
        
        $this->allow($permission, Scopes\Forum::ROLE_MODERATOR, Scopes\Topic::ACTION_DELETE);
        $this->allow($permission, Scopes\Forum::ROLE_MODERATOR, Scopes\Post::ACTION_DELETE);
        
        $this->allow($permission, Scopes\Forum::ROLE_MODERATOR, Scopes\Forum::ACTION_POST_UPDATE);
        $this->allow($permission, Scopes\Forum::ROLE_MODERATOR, Scopes\Forum::ACTION_TOPIC_UPDATE);
        

        
        $permission->allow(Identity::ROLE_ADMIN, Permission::ALL, Permission::ALL);

        return $permission;
    }

    /**
     * @param Permission $permission
     * @param            $role
     * @param array      $action
     */
    private function allow(Permission $permission, $role, array $action)
    {
        list($resource, $privilege) = $action;
        $permission->allow($role, $resource, $privilege);
    }
    
    /**
     * @param Permission $permission
     * @param            $role
     * @param array      $action
     */
    private function deny(Permission $permission, $role, array $action)
    {
        list($resource, $privilege) = $action;
        $permission->deny($role, $resource, $privilege);
    }
}

