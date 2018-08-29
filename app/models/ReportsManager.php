<?php

namespace App\Models;

use Dibi\Fluent;
use Dibi\Result;
use Nette\Utils\ArrayHash;

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
     *
     * @return Result|int
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
     *
     * @return Result|int
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
     *
     * @return Result|int
     */
    public function deleteByUser($user_id)
    {
        return $this->deleteFluent()
                ->where('[report_user_id] = %i', $user_id)
                ->execute();
    }
    
    /**
     *
     * @param int $topic_id
     *
     * @return Result|int
     */
    public function deleteByTopic($topic_id)
    {
        return $this->deleteFluent()
                ->where('[report_topic_id] = %i', $topic_id)
                ->execute();
    }    
     
    /**
     * 
     * @param int       $topic_id
     * @param ArrayHash $item_data
     * 
     * @return type
     */
    public function updateByTopic($topic_id, ArrayHash $item_data)
    {
        return $this->dibi->update($this->getTable(), $item_data)->where('[report_topic_id] = %i', $topic_id)->execute();
    }    
}
