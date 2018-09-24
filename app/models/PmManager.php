<?php

namespace App\Models;

use App\Models\Crud\CrudManager;
use Dibi\Fluent;
use \Nette\Security\User;

/**
 * Description of PMManager
 *
 * @author rendix2
 */
class PmManager extends CrudManager
{
    
    /**
     *
     * @var User $user
     * @inject
     */
    public $user;

    /**
     * @return Fluent
     */
    public function getAllFluent()
    {
        return parent::getAllFluent()
                ->as('pm')
                ->innerJoin(self::USERS_TABLE)
                ->as('u')
                ->on('pm.pm_user_id_from = u.user_id')
                ->where('pm.pm_user_id_to = %i', $this->user->id);
    }
    
    /**
     *
     * @return int
     */
    public function getCountSent()
    {
        return parent::getCountFluent()
                ->where('pm_user_id_to = %i', $this->user->id)
                ->where('pm_status = %s', 'sent')
                ->fetchSingle();
    }
}
