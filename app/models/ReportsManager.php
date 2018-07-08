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
        $this->dibi->delete($this->getTable())
                ->where('[report_post_id] = %i', $post_id)
                ->execute();
    }
    
    /**
     * 
     * @param int $forum_id
     */
    public function deleteByForum($forum_id)
    {
        $this->dibi->delete($this->getTable())
                ->where('[report_forum_id] = %i', $forum_id)
                ->execute();
    }    
    
    /**
     * 
     * @param int $user_id
     */
    public function deleteByUser($user_id)
    {
        $this->dibi->delete($this->getTable())
                ->where('[report_user_id] = %i', $user_id)
                ->execute();        
    }
}
