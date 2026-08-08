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
