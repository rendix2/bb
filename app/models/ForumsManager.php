<?php

namespace App\Models;

use Dibi\Fluent;
use Dibi\Row;

/**
 * Description of ForumManager
 *
 * @author rendi
 */
class ForumsManager extends Crud\CrudManager
{

    /**
     * @param int $category_id
     *
     * @return array
     */
    public function getForumsByCategoryId($category_id)
    {
        return $this->dibi->select('*')
                          ->from($this->getTable())
                          ->where('[forum_category_id] = %i', $category_id)
                          ->fetchAll();
    }

    /**
     * @param int $forum_id
     *
     * @return array
     */
    public function getForumsByForumParentId($forum_id)
    {
        return $this->dibi->select('*')
                          ->from($this->getTable())
                          ->where('[forum_parent_id] = %i', $forum_id)
                          ->fetchAll();
    }

    /**
     * @param int $forum_id
     *
     * @return Row|false
     */
    public function getParentForumByForumId($forum_id)
    {
        return $this->dibi->select('f2.*')
                          ->from($this->getTable())
                          ->as('f1')
                          ->innerJoin($this->getTable())
                          ->as('f2')
                          ->on('[f1.forum_parent_id] = [f2.forum_id]')
                          ->where('[f1.forum_id] = %i', $forum_id)
                          ->fetch();
    }

    /**
     * @param int $forum_id
     *
     * @return Fluent
     */
    public function getTopics($forum_id)
    {
        return $this->dibi->select('*')
                          ->from(self::TOPICS_TABLE)
                          ->as('t')
                          ->leftJoin(self::USERS_TABLE)
                          ->as('u')
                          ->on('[t.topic_user_id] = [u.user_id]')
                          ->where('[t.topic_forum_id] = %i', $forum_id);
    }

    /**
     * @param iterable $forums
     * @param int $forum_parent_id
     *
     * @return array
     */
    public function createForums($forums, $forum_parent_id)
    {
        $result = [];

        foreach ($forums as $forum) {
            if ($forum->forum_parent_id === $forum_parent_id) {
                $result[$forum->forum_id]           = $forum;
                $result[$forum->forum_id]['childs'] = $this->createForums($forums, $forum->forum_id);
            }
        }

        return $result;
    }

}
