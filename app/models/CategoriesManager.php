<?php

namespace App\Models;

/**
 * Description of CategoriesManager
 *
 * @author rendi
 */
class CategoriesManager extends Crud\CrudManager {
    
    public function getForSelect(){
        return $this->dibi->select('category_id, category_name')->from($this->getTable())->fetchPairs('category_id', 'category_name');
    }
    
    public function getByForumId($forum_id){
        return $this->dibi->select('*')->from(self::CATEGORIES_TABLE)->as('c')->leftJoin(self::FORUM_TABLE)->as('f')->on('[f.forum_category_id] = [c.category_id]')->where('[f.forum_id] = %i', $forum_id)->fetch();
    }
    
}
