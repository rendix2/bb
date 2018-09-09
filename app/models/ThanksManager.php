<?php

namespace App\Models;

use Dibi\Fluent;
use Dibi\Result;
use Dibi\Row;
use Nette\Utils\ArrayHash;

/**
 * Description of ThanksManager
 *
 * @author rendix2
 */
class ThanksManager extends Crud\CrudManager
{
    /**
     * @param int $forum_id
     *
     * @return array []
     */
    public function getThanksByForum($forum_id)
    {
        return $this->getAllFluent()
            ->where('[thank_forum_id] = %i', $forum_id)
            ->fetchAll();
    }

    /**
     * @param int $topic_id
     *
     * @return Row[]
     */
    public function getThanksByTopic($topic_id)
    {
        return $this->getAllFluent()
            ->where('[thank_topic_id] = %i', $topic_id)
            ->fetchAll();
    }

    /**
     * @param int $user_id
     *
     * @return Row[]
     */
    public function getThanksByUser($user_id)
    {
        return $this->getAllFluent()
            ->where('[thank_user_id] = %i', $user_id)
            ->fetchAll();
    }

    /**
     * @param int $topic_id
     *
     * @return Row[]
     */
    public function getThanksJoinedUserByTopic($topic_id)
    {
        return $this->getAllFluent()
            ->as('t')
            ->innerJoin(self::USERS_TABLE)
            ->as('u')
            ->on('[u.user_id] = [t.thank_user_id]')
            ->where('[t.thank_topic_id] = %i', $topic_id)
            ->fetchAll();
    }

    /**
     * @param int $user_id
     *
     * @return Fluent
     */
    public function getThanks($user_id)
    {
        return $this->getAllFluent()
            ->as('th')
            ->innerJoin(self::TOPICS_TABLE)
            ->as('to')
            ->on('[th.thank_topic_id] = [to.topic_id]')
            ->where('[th.thank_user_id] = %i', $user_id);
    }

    /**
     * @param int $forum_id
     * @param int $topic_id
     * @param int $user_id
     *
     * @return bool
     */
    public function canUserThank($forum_id, $topic_id, $user_id)
    {
        return !$this->dibi
            ->select('1')
            ->from($this->getTable())
            ->where('[thank_forum_id] = %i', $forum_id)
            ->where('[thank_topic_id] = %i', $topic_id)
            ->where('[thank_user_id] = %i', $user_id)
            ->fetch();
    }

    /**
     * @param int $topic_id
     *
     * @return Result|int
     */
    public function deleteByTopic($topic_id)
    {
        return $this->deleteFluent()
            ->where('[thank_topic_id] = %i', $topic_id)
            ->execute();
    }
    
    /**
     * @param int $user_id
     *
     * @return Result|int
     */
    public function deleteByUser($user_id)
    {
        return $this->deleteFluent()
            ->where('[thank_user_id] = %i', $user_id)
            ->execute();
    }
    
    /**
     * 
     * @param type $topic_id
     * @param ArrayHash $item_data
     * 
     * @return type
     */
    public function updateByTopic($topic_id, ArrayHash $item_data)
    {
        return $this->dibi
            ->update($this->getTable(), $item_data)
            ->where('[thank_topic_id] = %i', $topic_id)
            ->execute();                
    }
    
    /**
     * 
     * @param type $user_id
     * @param ArrayHash $item_data
     * 
     * @return type
     */
    public function updateByUser($user_id, ArrayHash $item_data)
    {
        return $this->dibi
            ->update($this->getTable(), $item_data)
            ->where('[thank_user_id] = %i', $user_id)
            ->execute();                
    }    
    
    /**
     * 
     * @param array $user_id
     * @param ArrayHash $item_data
     * 
     * @return type
     */
    public function updateMultiByUser(array $user_id, ArrayHash $item_data)
    {
        return $this->dibi
            ->update($this->getTable(), $item_data)
            ->where('[thank_user_id] IN %in', $user_id)
            ->execute();                
    } 
    
    /**
     * 
     * @param array $user_ids
     * @param int   $topic_id
     * 
     * @return type
     */
    public function deleteByUserAndTopic(array $user_ids, $topic_id)
    {
        return $this->dibi
                ->delete($this->getTable())
                ->where('[thank_user_id] IN %in', $user_ids)
                ->where('[thank_topic_id] = %i', $topic_id)
                ->execute();
    }
    
    public function getByUserAndTopic($user_id, $topic_id)
    {
        return $this->dibi->select('*')
                ->from($this->getTable())
                ->where('[thank_user_id] = %i', $user_id)
                ->where('[thank_topic_id] = %i', $topic_id)
                ->fetch();
    }
}
