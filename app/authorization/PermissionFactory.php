<?php

namespace App\Authorization;

use App\Authorization\Scopes\CategoryScope;
use App\Authorization\Scopes\ForumScope;
use App\Authorization\Scopes\PostScope;
use App\Authorization\Scopes\TopicScope;
use Nette\Security\Permission;

/**
 * Description of PermissionFactory
 *
 * @author rendix2
 * @package App\Authorization
 */
class PermissionFactory
{
    /**
     * @return Permission
     */
    public function create()
    {
        $permission = new Permission();
        
        $permission->addResource(CategoryScope::class);
        $permission->addResource(ForumScope::class);
        $permission->addResource(TopicScope::class);
        $permission->addResource(PostScope::class);
        
        $permission->addRole(TopicScope::ROLE_THANKER); // can thank
        $permission->addRole(TopicScope::ROLE_NOT_THANKER); // cant thank
        
        $permission->addRole(Identity::ROLE_HOST);
        $permission->addRole(Identity::ROLE_REGISTERED, [Identity::ROLE_HOST, TopicScope::ROLE_NOT_THANKER]);
                
        $permission->addRole(ForumScope::ROLE_FORUM_VIEWER, [Identity::ROLE_REGISTERED]);
        $permission->addRole(ForumScope::ROLE_FORUM_THANKER, [Identity::ROLE_REGISTERED]);
        $permission->addRole(ForumScope::ROLE_FORUM_FAST_REPLIER, [Identity::ROLE_REGISTERED]);
        
        $permission->addRole(ForumScope::ROLE_FORUM_POST_ADDER, [Identity::ROLE_REGISTERED]);
        $permission->addRole(ForumScope::ROLE_FORUM_POST_UPDATER, [Identity::ROLE_REGISTERED]);
        $permission->addRole(ForumScope::ROLE_FORUM_POST_DELETER, [Identity::ROLE_REGISTERED]);
        
        $permission->addRole(ForumScope::ROLE_FORUM_TOPIC_ADDER, [Identity::ROLE_REGISTERED]);
        $permission->addRole(ForumScope::ROLE_FORUM_TOPIC_UPDATER, [Identity::ROLE_REGISTERED]);
        $permission->addRole(ForumScope::ROLE_FORUM_TOPIC_DELETER, [Identity::ROLE_REGISTERED]);
        
        $permission->addRole(PostScope::ROLE_AUTHOR, [Identity::ROLE_REGISTERED]);
        $permission->addRole(TopicScope::ROLE_AUTHOR, [Identity::ROLE_REGISTERED]);
        
        $permission->addRole(PostScope::ROLE_EDITOR, [Identity::ROLE_REGISTERED]);
        $permission->addRole(TopicScope::ROLE_EDITOR, [Identity::ROLE_REGISTERED]);
        
        $permission->addRole(PostScope::ROLE_DELETER, [Identity::ROLE_REGISTERED]);
        $permission->addRole(TopicScope::ROLE_DELETER, [Identity::ROLE_REGISTERED]);
        
        $permission->addRole(PostScope::ROLE_HISTORIER);
        
        $permission->addRole(ForumScope::ROLE_MODERATOR, [PostScope::ROLE_AUTHOR, PostScope::ROLE_DELETER, PostScope::ROLE_EDITOR, TopicScope::ROLE_AUTHOR, TopicScope::ROLE_DELETER, TopicScope::ROLE_EDITOR]);
        
        $permission->addRole(Identity::ROLE_ADMIN, [ForumScope::ROLE_MODERATOR]);
        
        $this->deny($permission, TopicScope::ROLE_NOT_THANKER, TopicScope::ACTION_THANK);
        //$this->deny($permission, Identity::ROLE_REGISTERED, Scopes\Post::ACTION_DELETE);
        //$this->deny($permission, Identity::ROLE_REGISTERED, Scopes\Topic::ACTION_DELETE);
        
        
        $this->allow($permission, Identity::ROLE_HOST, PostScope::ACTION_VIEW);
        $this->allow($permission, Identity::ROLE_HOST, TopicScope::ACTION_VIEW);
        $this->allow($permission, Identity::ROLE_HOST, ForumScope::ACTION_VIEW);
               
        $this->allow($permission, ForumScope::ROLE_FORUM_POST_ADDER, ForumScope::ACTION_POST_ADD);  
        $this->allow($permission, ForumScope::ROLE_FORUM_TOPIC_ADDER, ForumScope::ACTION_TOPIC_ADD);
        
        //$this->allow($permission, Scopes\Forum::ROLE_FORUM_POST_UPDATER, Scopes\Forum::ACTION_POST_UPDATE);
        //$this->allow($permission, Scopes\Forum::ROLE_FORUM_TOPIC_UPDATER, Scopes\Forum::ACTION_TOPIC_UPDATE);
        
        $this->allow($permission, ForumScope::ROLE_FORUM_POST_DELETER, ForumScope::ACTION_POST_DELETE);
        $this->allow($permission, ForumScope::ROLE_FORUM_TOPIC_DELETER, ForumScope::ACTION_TOPIC_DELETE);
        
        $this->allow($permission, ForumScope::ROLE_FORUM_THANKER, ForumScope::ACTION_THANK);
        $this->allow($permission, TopicScope::ROLE_THANKER, TopicScope::ACTION_THANK);
        
        $this->allow($permission, PostScope::ROLE_EDITOR, PostScope::ACTION_EDIT);
        $this->allow($permission, TopicScope::ROLE_EDITOR, TopicScope::ACTION_EDIT);
                
        $this->allow($permission, PostScope::ROLE_DELETER, PostScope::ACTION_DELETE);                     
        $this->allow($permission, TopicScope::ROLE_DELETER, TopicScope::ACTION_DELETE);
        
        $this->allow($permission, PostScope::ROLE_HISTORIER, PostScope::ACTION_HISTORY);
        
        $this->allow($permission, ForumScope::ROLE_MODERATOR, TopicScope::ACTION_DELETE);
        $this->allow($permission, ForumScope::ROLE_MODERATOR, PostScope::ACTION_DELETE);
        
        $this->allow($permission, ForumScope::ROLE_MODERATOR, ForumScope::ACTION_POST_UPDATE);
        $this->allow($permission, ForumScope::ROLE_MODERATOR, ForumScope::ACTION_TOPIC_UPDATE);
        

        
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

