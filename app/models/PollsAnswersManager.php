<?php

namespace App\Models;

use App\Models\Crud\CrudManager;

/**
 * Description of PollsAnswersManager
 *
 * @author rendix2
 */
class PollsAnswersManager extends CrudManager
{
    /**
     * @param int $poll_id
     *
     * @return \Dibi\Result|int
     */
    public function deleteByPoll($poll_id)
    {
        return $this->deleteFluent()
            ->where('[poll_id] = %i', $poll_id)
            ->execute();
    }

    /**
     * @param int $poll_id
     *
     * @return array
     */
    public function getAllByPoll($poll_id)
    {
        return $this->getAllFluent()
            ->where('[poll_id] = %i', $poll_id)
            ->fetchAll();
    }
}
