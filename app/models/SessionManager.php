<?php

namespace App\Models;

use App\Models\Crud\CrudManager;
use Dibi\Result;
use Dibi\Row;
use Nette\Utils\ArrayHash;

/**
 * Description of SessionsManager
 *
 * @author rendix2
 * @package App\Models
 */
class SessionManager extends CrudManager
{

    /**
     * @param int $user_id
     *
     * @return Result|int
     */
    public function deleteByUser($user_id)
    {
        return $this->deleteFluent()
            ->where('[session_user_id] = %i', $user_id)
            ->execute();
    }
}
