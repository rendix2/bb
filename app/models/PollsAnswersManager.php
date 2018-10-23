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
    
    public function deleteByPoll($poll_id)
    {
        return $this->deleteFluent()
                ->where('[poll_id] = %i', $poll_id)
                ->execute();
    }
}
