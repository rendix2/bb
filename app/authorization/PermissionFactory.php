<?php

namespace App\Authorization;

use Nette\Security\Permission;

/**
 * Description of PermissionFactory
 *
 * @author rendix2
 */
class PermissionFactory {
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
        
        $permission->addRole(Scopes\Post::ROLE_AUTHOR);
        $permission->addRole(Scopes\Topic::ROLE_AUTHOR);        
        $permission->addRole(Scopes\Forum::ROLE_MODERATOR, [Scopes\Post::ROLE_AUTHOR, Scopes\Topic::ROLE_AUTHOR]);
        $permission->addRole(Identity::ROLE_ADMIN, [Scopes\Forum::ROLE_MODERATOR]);
        
        $this->allow($permission, Scopes\Post::ROLE_AUTHOR, Scopes\Post::ACTION_EDIT);
        $this->allow($permission, Scopes\Topic::ROLE_AUTHOR, Scopes\Topic::ACTION_EDIT);
        
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
}

