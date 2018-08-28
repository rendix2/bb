<?php

namespace App\Models;

use App\Models\Crud\CrudManager;
use Dibi\Fluent;

/**
 * Description of PMManager
 *
 * @author rendix2
 */
class PmManager extends CrudManager
{
    /**
     * @return Fluent
     */
    public function getAllFluent()
    {
        return parent::getAllFluent()
                ->as('pm')
                ->innerJoin(self::USERS_TABLE)
                ->as('u')
                ->on('pm.pm_user_id_to = u.user_id');
    }
}
