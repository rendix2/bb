<?php

namespace App\Models;

use Dibi\Row;

/**
 * Class CategoriesManager
 * @package App\Models
 */
class CategoriesManager extends Crud\CrudManager
{

    /**
     * @param $forum_id
     *
     * @return Row|false
     */
    public function getByForumId($forum_id)
    {
        return $this->dibi->select('*')
                          ->from(self::CATEGORIES_TABLE)
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
    public function getForSelect()
    {
        return $this->dibi->select('category_id, category_name')
                          ->from($this->getTable())
                          ->fetchPairs('category_id', 'category_name');
    }

}
