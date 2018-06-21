<?php

namespace App\Models;

use Dibi\Fluent;

/**
 * Description of ReportsManager
 *
 * @author rendi
 */
class ReportsManager extends Crud\CrudManager
{

    /**
     * @return Fluent
     */
    public function getAllFluent()
    {
        //->where('report_status = %i', 1)
        return parent::getAllFluent()
            ->innerJoin(self::FORUM_TABLE)
            ->as('f')
            ->on('[f.forum_id] = [report_forum_id]')
            ->innerJoin(self::TOPICS_TABLE)
            ->as('t')
            ->on('[report_topic_id] = t.topic_id')
            ->innerJoin(self::USERS_TABLE)
            ->as('u')
            ->on('report_user_id = u.user_id')
            ->leftJoin(self::POSTS_TABLES)
            ->as('p')
            ->on('report_post_id = p.post_id');
    }

    /**
     * @param int $forum_id
     *
     * @return Fluent
     */
    public function getByForumId($forum_id)
    {
        return $this->getAllFluent()
                ->where('[report_forum_id] = %i', $forum_id);
    }

    /**
     * @param int $topic_id
     *
     * @return Fluent
     */
    public function getByTopicId($topic_id)
    {
        return $this->getAllFluent()
                ->where('[report_topic_id] = %i', $topic_id);
    }

    /**
     * @param int $user_id
     *
     * @return Fluent
     */
    public function getByUserId($user_id)
    {
        return $this->getAllFluent()
                ->where('[report_user_id] = %i', $user_id);
    }
}
