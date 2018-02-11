<?php

namespace App;

/**
 * Description of Authorizator
 *
 * @author rendi
 */
class Authorizator {

    private $acl;
    private $forumManager;

    public function __construct(Models\ForumsManager $forumsManager) {
        $this->acl = new \Nette\Security\Permission();
        $this->forumManager = $forumsManager;    
        
        $this->defineRoles();
        $this->defineResources();
        $this->definePrivilegies();
    }
    
    public function getAcl() {
        return $this->acl;
    }

    private function defineRoles() {
        $this->acl->addRole('geust');
        $this->acl->addRole('registered', 'geust');
        $this->acl->addRole('moderator', 'registered');
        $this->acl->addRole('juniorAdmin', 'moderator');
        $this->acl->addRole('admin', 'juniorAdmin');       
    }

    private function defineResources() {
        foreach ($this->forumManager->getAllCached() as $forum) {
            $this->acl->addResource("" . $forum->forum_id);
            $this->acl->allow('geust', "" . $forum->forum_id, 'forum_view');

            if ($forum->forum_thank) {
                $this->acl->allow('registered', "" . $forum->forum_id, 'topic_thank');
            }

            if ($forum->forum_post_add) {
                $this->acl->allow('registered', "" . $forum->forum_id, 'post_add');
            }

            if ($forum->forum_post_delete) {
                $this->acl->allow('registered', "" . $forum->forum_id, 'post_delete');
            }
            
            if ( $forum->forum_post_update ){
                $this->acl->allow('registered', "" . $forum->forum_id, 'post_update');
            }
            
            if ( $forum->forum_topic_add ){
                $this->acl->allow('registered', "" . $forum->forum_id, 'topic_add');
            }
        }
    }
    
    private function definePrivilegies(){
        
        $this->acl->deny('geust', \Nette\Security\Permission::ALL, 'topic_thank');
        $this->acl->deny('geust', \Nette\Security\Permission::ALL, 'post_add');
        $this->acl->deny('geust', \Nette\Security\Permission::ALL, 'post_delete');  
                       
        // moderator
        $this->acl->allow('moderator', \Nette\Security\Permission::ALL, 'post_delete');  
        $this->acl->allow('moderator', \Nette\Security\Permission::ALL, 'post_update');  
        $this->acl->allow('moderator', \Nette\Security\Permission::ALL, 'topic_thank');
        
        // addmin
        $this->acl->allow('admin', \Nette\Security\Permission::ALL, 'post_delete');
        $this->acl->allow('admin', \Nette\Security\Permission::ALL, 'post_add');   
        $this->acl->allow('admin', \Nette\Security\Permission::ALL, 'post_update');   
        $this->acl->allow('admin', \Nette\Security\Permission::ALL, 'topic_thank');
        $this->acl->allow('admin', \Nette\Security\Permission::ALL, 'topic_add');
        $this->acl->allow('admin', \Nette\Security\Permission::ALL, 'topic_edit');
        
    }

}
