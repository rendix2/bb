<?php

namespace App\Models;

use Dibi\Fluent;

/**
 * Description of ReportsManager
 *
 * @author rendix2
 */
class ReportsManager extends Crud\CrudManager
{

    /**
     * @return Fluent
     */
    public function getAllFluent()
    {
        return parent::getAllFluent()
            ->innerJoin(self::FORUM_TABLE)
            ->as('f')
            ->on('[f.forum_id] = [report_forum_id]')
            ->leftJoin(self::TOPICS_TABLE)
            ->as('t')
            ->on('[report_topic_id] = t.topic_id')
            ->leftJoin(self::USERS_TABLE)
            ->as('u')
            ->on('report_user_id = u.user_id')
            ->leftJoin(self::POSTS_TABLE)
            ->as('p')
            ->on('report_post_id = p.post_id');
    }
    
    /**
     *
     * @param int $post_id
     */
    public function deleteByPost($post_id)
    {
        return $this->deleteFluent()
                ->where('[report_post_id] = %i', $post_id)
                ->execute();
    }
    
    /**
     *
     * @param int $forum_id
     */
    public function deleteByForum($forum_id)
    {
        return $this->deleteFluent()
                ->where('[report_forum_id] = %i', $forum_id)
                ->execute();
    }
    
    /**
     *
     * @param int $user_id
     */
    public function deleteByUser($user_id)
    {
        return $this->deleteFluent()
                ->where('[report_user_id] = %i', $user_id)
                ->execute();
    }
}
