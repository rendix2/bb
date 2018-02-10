<?php

namespace App\Models;

/**
 * Description of UserManager
 *
 * @author rendi
 */
class UsersManager extends Crud\CrudManager {
    
    public function getByName($user_name){
        return $this->dibi->select('*')->from($this->getTable())->where('[user_name] = %s', $user_name)->fetch();
    }   
    
    public function getTopics($user_id){
        return $this->dibi->select('*')->from(self::TOPICS_TABLE)->where('[topic_user_id] = %i', $user_id)->fetchAll();
    }
    
    public function getPosts($user_id){
        return $this->dibi->select('*')->from(self::POSTS_TABLES)->where('[post_user_id] = %i', $user_id)->fetchAll();
    }
    
    public function getThanks($user_id){
        return $this->dibi->select('*')->from(self::THANKS_TABLE)->as('th')->innerJoin(self::TOPICS_TABLE)->as('to')->on('[th.thank_topic_id] = [to.topic_id]')->where('[th.thank_user_id] = %i', $user_id)->fetchAll();
    }
    
    public function findUsersByUserName($user_name){
       return $this->dibi->select('*')->from($this->getTable())->where('[user_name] LIKE %~like~', $user_name)->fetchAll(); 
    }
    
    public function getRoles($user_id){
        return $this->dibi->select('*')->from(self::USERS2ROLES_TABLE)->as('ur')->innerJoin(self::ROLES_TABLE)->as('r')->on('[r.role_id] = [ur.role_id]')->where('[ur.user_id] = %i', $user_id)->fetchPairs('role_id', 'role_name');        
    }
    
    public function getByRoleId($role_id){
        return $this->dibi->select('*')->from($this->getTable())->where('[user_role_id] = %i', $role_id)->fetchAll();
    }
    
    public function getCountByRoleId($role_id){
        return $this->dibi->select('COUNT(*)')->from($this->getTable())->where('[user_role_id] = %i', $role_id)->fetchSingle()  ;
    }
       
}
