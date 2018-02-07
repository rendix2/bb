<?php

namespace App\Models;

/**
 * Description of IndexManager
 *
 * @author rendi
 */
class IndexManager extends Manager {

    public function getActiveCategories() {
        return $this->dibi->select('*')->from(self::CATEGORIES_TABLE)->where('[category_active] = %i', 1)->orderBy('category_order', \dibi::ASC)->fetchAll();
    }
    
    public function getForumByCategoryId($category_id){
        return $this->dibi->select('*')->from(self::FORUM_TABLE)->where('[forum_category_id] = %i', $category_id)->orderBy('forum_order', \dibi::ASC)->fetchAll();
    }

    public function getForumsFirstLevel($category_id) {
        return $this->dibi->select('*')->from(self::FORUM_TABLE)->where('[forum_category_id] = %i', $category_id)->where('[forum_active] = %i', 1)->where('forum_parent_id = %i', 0)->orderBy('forum_order', \dibi::ASC)->fetchAll();
    }
    
    public function getTotalTopics(){
        return $this->dibi->select('COUNT(topic_id)')->from(self::TOPICS_TABLE)->fetchSingle();                
    }
    
    public function getTotalPosts(){
        return $this->dibi->select('COUNT(post_id)')->from(self::POSTS_TABLES)->fetchSingle();
    }           
    
    public function getTotalUsers(){
        return $this->dibi->select('COUNT(user_id)')->from(self::USERS_TABLE)->fetchSingle();
    }  
    
    public function getLastUser(){
        return $this->dibi->select('user_id, user_name')->from(self::USERS_TABLE)->orderBy('user_id', \dibi::DESC)->fetch();
    }
    
        public function getLastTopic(){
        return $this->dibi->select('topic_id, topic_forum_id, topic_name')->from(self::TOPICS_TABLE)->orderBy('topic_id', \dibi::DESC)->fetch();
    }
}
