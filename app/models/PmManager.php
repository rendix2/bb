<?php

namespace App\Models;

use App\Models\Crud\CrudManager;

/**
 * Description of PMManager
 *
 * @author rendi
 */
class PmManager extends CrudManager
{
    public function getAllFluent() {
        return parent::getAllFluent()
                ->as('pm')
                ->innerJoin(self::USERS_TABLE)
                ->as('u')
                ->on('pm.pm_user_id_to = u.user_id');
    }
}