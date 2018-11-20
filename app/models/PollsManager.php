<?php

namespace App\Models;

use App\Models\Crud\CrudManager;
use Dibi\Row;

/**
 * Description of PollsManager
 *
 * @author rendix2
 * @package App\Models
 */
class PollsManager extends CrudManager
{

    /**
     *
     * @param int $topic_id
     *
     * @return Row
     */
    public function getByTopic($topic_id)
    {
        return $this->getAllFluent()
                ->where('[poll_topic_id] = %i', $topic_id)
                ->fetch();
    }
}
