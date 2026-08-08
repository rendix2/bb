<?php

namespace App\Models;

use App\Models\Crud\CrudManager;
use Dibi\Connection;
use Dibi\Fluent;
use Exception;
use Nette\Caching\IStorage;
use Nette\Security\User;

/**
 * Description of PMManager
 *
 * @author rendix2
 * @package App\Models
 */
#[\JetBrains\PhpStorm\Deprecated]

class PmManager extends CrudManager
{

    public function __construct(
        Connection $dibi,
        IStorage   $storage,
        private User       $user
    ) {
        parent::__construct($dibi, $storage);

    }

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
            ->where('pm.pm_user_id_to = %i', $this->user->getId());
    }
    
    /**
     *
     * @return int
     */
    public function getCountSent()
    {
        return parent::getCountFluent()
            ->where('pm_user_id_to = %i', $this->user->getId())
            ->where('pm_status = %s', 'sent')
            ->fetchSingle();
    }

}
