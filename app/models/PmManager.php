<?php

namespace App\Models;

use App\Models\Crud\CrudManager;

/**
 * Description of PMManager
 *
 * @author rendix2
 */
class PmManager extends CrudManager
{
    
    /**
     *
     * @var \Nette\Security\User $user
     * @inject
     */
    public $user;

    public function getAllFluent()
    {
        return parent::getAllFluent()
                ->as('pm')
                ->innerJoin(self::USERS_TABLE)
                ->as('u')
                ->on('pm.pm_user_id_to = u.user_id')
                ->where('pm.pm_user_id_to = %i', $this->user->id);
    }
    
    public function getCountSent()
    {
        return parent::getCountFluent()
                ->where('pm_user_id_to = %i', $this->user->id)
                ->where('pm_status = %s', 'sent')
                ->fetchSingle();        
    }
}