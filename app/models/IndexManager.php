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
     * @return array
     */
    public function getActiveCategories()
    {
        return $this->dibi->select('*')
                          ->from(self::CATEGORIES_TABLE)
                          ->where('[category_active] = %i', 1)
                          ->orderBy('category_order', dibi::ASC)
                          ->fetchAll();
    }

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
    public function getLastTopic()
    {
        return $this->dibi->query('SELECT * FROM ['.self::TOPICS_TABLE.'] WHERE [topic_id] = (SELECT MAX(topic_id) FROM ['.self::TOPICS_TABLE.'])')->fetch();
    }

    /**
     * @return Row|false
     */
    public function getLastUser()
    {
        return $this->dibi->query('SELECT * FROM ['.self::USERS_TABLE.'] WHERE [user_id] = (SELECT MAX(user_id) FROM ['.self::USERS_TABLE.'])')->fetch();
    }

    /**
     * @return int
     */
    public function getTotalPosts()
    {
        return $this->dibi->select('COUNT(post_id)')->from(self::POSTS_TABLES)->fetchSingle();
    }

    /**
     * @return int
     */
    public function getTotalTopics()
    {
        return $this->dibi->select('COUNT(topic_id)')->from(self::TOPICS_TABLE)->fetchSingle();
    }

    /**
     * @return int
     */
    public function getTotalUsers()
    {
        return $this->dibi->select('COUNT(user_id)')->from(self::USERS_TABLE)->fetchSingle();
    }
}
