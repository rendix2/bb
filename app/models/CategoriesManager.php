<?php

namespace App\Models;

use Dibi\Row;
use dibi;
use Nette\Caching\Cache;

/**
 * Class CategoriesManager
 * @package App\Models
 */
class CategoriesManager extends Crud\CrudManager
{

    /**
     * @param int $forum_id
     *
     * @return Row|false
     */
    public function getByForumId($forum_id)
    {
        return $this->dibi->select('*')
                          ->from($this->getTable())
                          ->as('c')
                          ->leftJoin(self::FORUM_TABLE)
                          ->as('f')
                          ->on('[f.forum_category_id] = [c.category_id]')
                          ->where('[f.forum_id] = %i', $forum_id)
                          ->fetch();
    }
    
     /**
     * @return array
     */
    public function getActiveCategories()
    {
        return $this->dibi->select('*')
                          ->from($this->getTable())
                          ->where('[category_active] = %i', 1)
                          ->orderBy('category_order', dibi::ASC)
                          ->fetchAll();
    }

    /**
     * @return array|mixed
     */
    public function getActiveCategoriesCached(){
        $key    = 'ActiveCategories';
        $cache  = new Cache($this->getStorage(), $this->getTable());
        $cached = $cache->load($key);
               
        if ( !$cached ){
            $cache->save($key, $cached = $this->getActiveCategories());
        }
        
        return $cached;        
    }
    
    public function move(){
        
    }
}
