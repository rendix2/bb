<?php

namespace App\Models;

use App\Models\Crud\CrudManager;
use Dibi\Fluent;
use Dibi\Result;
use Dibi\Row;
use Nette\Utils\ArrayHash;

/**
 * Description of ThanksManager
 *
 * @author rendix2
 * @package App\Models
 */
class ThankManager extends CrudManager
{


    /**
     * @param int $topic_id
     *
     * @return Row[]
     */
    public function getAllByTopic($topic_id)
    {
        return $this->getAllFluent()
            ->where('[thank_topic_id] = %i', $topic_id)
            ->fetchAll();
    }

    /**
     * @param int $topic_id
     *
     * @return Row[]
     */
    public function getAllByTopicJoinedUser($topic_id)
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
    public function getFluentByUserJoinedTopic($user_id)
    {
        return $this->getAllFluent()
            ->as('th')
            ->innerJoin(self::TOPICS_TABLE)
            ->as('to')
            ->on('[th.thank_topic_id] = [to.topic_id]')
            ->where('[th.thank_user_id] = %i', $user_id);
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
     *
     * @param int       $topic_id
     * @param ArrayHash $item_data
     *
     * @return bool
     */
    public function updateByTopic($topic_id, ArrayHash $item_data)
    {
        return $this->updateFluent($item_data)
            ->where('[thank_topic_id] = %i', $topic_id)
            ->execute();
    }
    
    /**
     *
     * @param array     $user_id
     * @param ArrayHash $item_data
     *
     * @return bool
     */
    public function updateMultiByUser(array $user_id, ArrayHash $item_data)
    {
        return $this->updateFluent($item_data)
            ->where('[thank_user_id] IN %in', $user_id)
            ->execute();
    }
    
    /**
     *
     * @param array $user_ids
     * @param int   $topic_id
     *
     * @return bool
     */
    public function deleteByUsersAndTopic(array $user_ids, int $topic_id)
    {
        return $this->deleteFluent()
                ->where('[thank_user_id] IN %in', $user_ids)
                ->where('[thank_topic_id] = %i', $topic_id)
                ->execute();
    }

}
