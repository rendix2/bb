<?php

namespace App\Models;

use Dibi\Fluent;
use Dibi\Result;
use Dibi\Row;

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
     * @return array [[]
     */
    public function getThanksByForum($forum_id)
    {
        return $this->dibi
            ->select('*')
            ->from($this->getTable())
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
        return $this->dibi
            ->select('*')
            ->from($this->getTable())
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
        return $this->dibi
            ->select('*')
            ->from($this->getTable())
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
        return $this->dibi
            ->select('*')
            ->from($this->getTable())
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
        return $this->dibi->select('*')
            ->from($this->getTable())
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
        return $this->dibi
            ->delete($this->getTable())
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
        return $this->dibi
            ->delete($this->getTable())
            ->where('[thank_user_id] = %i', $user_id)
            ->execute();
    }
}
