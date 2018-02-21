<?php

namespace App\Models;

use dibi;
use Dibi\Row;

/**
 * Description of IndexManager
 *
 * @author rendi
 */
class IndexManager extends Manager
{

    /**
     * @param int $category_id
     *
     * @return array
     */
    public function getForumByCategoryId($category_id)
    {
        return $this->dibi->select('*')
                          ->from(self::FORUM_TABLE)
                          ->where('[forum_category_id] = %i', $category_id)
                          ->orderBy('forum_order', dibi::ASC)
                          ->fetchAll();
    }

    /**
     * @param int $category_id
     *
     * @return array
     */
    public function getForumsFirstLevel($category_id)
    {
        return $this->dibi->select('*')
                          ->from(self::FORUM_TABLE)
                          ->where('[forum_category_id] = %i', $category_id)
                          ->where('[forum_active] = %i', 1)
                          ->where('forum_parent_id = %i', 0)
                          ->orderBy('forum_order', dibi::ASC)
                          ->fetchAll();
    }

    /**
     * @return Row|false
     */
    public function getUserWithMostPosts()
    {
        return $this->dibi->select('count(p.post_id) as post_count, u.user_id, u.user_name')
                          ->from(self::POSTS_TABLES)
                          ->as('p')
                          ->innerJoin(self::USERS_TABLE)
                          ->as('u')
                          ->on('[p.post_user_id] = [u.user_id]')
                          ->groupBy('post_user_id', dibi::ASC)
                          ->fetch();
    }
}
