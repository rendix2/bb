<?php

namespace App\Models;

/**
 * Description of ForumManager
 *
 * @author rendi
 */
class ForumsManager extends Crud\CrudManager {

    public function getTopics($forum_id) {
        return $this->dibi->select('*')->from(self::TOPICS_TABLE)->as('t')->leftJoin(self::USERS_TABLE)->as('u')->on('[t.topic_user_id] = [u.user_id]')->where('[t.topic_forum_id] = %i', $forum_id);
    }

    public function getForumsByForumParentId($forum_id) {
        return $this->dibi->select('*')->from($this->getTable())->where('[forum_parent_id] = %i', $forum_id)->fetchAll();
    }

    public function getParentForumByForumId($forum_id) {
        return $this->dibi->select('f2.*')->from($this->getTable())->as('f1')->innerJoin($this->getTable())->as('f2')->on('[f1.forum_parent_id] = [f2.forum_id]')->where('[f1.forum_id] = %i', $forum_id)->fetch();
    }

    public function getForumsByCategoryId($category_id) {
        return $this->dibi->select('*')->from($this->getTable())->where('[forum_category_id] = %i', $category_id)->fetchAll();
    }

    public function createForums($forums, $forum_parent_id) {
        $result = [];

        foreach ($forums as $forum) {
            if ($forum->forum_parent_id === $forum_parent_id) {
                $result[$forum->forum_id] = $forum;
                $result[$forum->forum_id]['childs'] = $this->createForums($forums, $forum->forum_id);
            }
        }

        return $result;
    }

}
