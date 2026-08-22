<?php

namespace App\Models;

use App\Models\Crud\CrudManager;

/**
 * Description of ThanksManager
 *
 * @author rendix2
 * @package App\Models
 */
class ThankManager extends CrudManager
{

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
