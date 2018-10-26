<?php

namespace App\Models;

use App\Models\Crud\CrudManager;

/**
 * Description of PollVotesManager
 *
 * @author rendix2
 */
class PollsVotesManager extends CrudManager
{
    
    public function getAllByPoll($poll_id) 
    {
        return $this->getAllFluent()
                ->where('[poll_id] = %i', $poll_id)
                ->fetchAll();
    }
    
    public function deleteByPoll($poll_id)
    {
        return $this->deleteFluent()
            ->where('[poll_id] = %i', $poll_id)
            ->execute();
    }
}
