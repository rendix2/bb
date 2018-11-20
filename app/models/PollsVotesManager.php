<?php

namespace App\Models;

use App\Models\Crud\CrudManager;
use Dibi\Row;

/**
 * Description of PollVotesManager
 *
 * @author rendix2
 * @package App\Models
 */
class PollsVotesManager extends CrudManager
{
    
    /**
     * 
     * @param int $poll_id
     * 
     * @return Row[]
     */
    public function getAllByPoll($poll_id) 
    {
        return $this->getAllFluent()
                ->where('[poll_id] = %i', $poll_id)
                ->fetchAll();
    }
    
    /**
     * 
     * @param int $poll_id
     * 
     * @return bool
     */
    public function deleteByPoll($poll_id)
    {
        return $this->deleteFluent()
            ->where('[poll_id] = %i', $poll_id)
            ->execute();
    }
}
