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
class PmManager extends CrudManager
{
   
    /**
     *
     * @var User $user
     */
    private $user;

    /**
     * PmManager constructor.
     *
     * @param Connection $dibi
     * @param IStorage   $storage
     * @param User       $user
     *
     * @throws Exception
     */
    public function __construct(
        Connection $dibi,
        IStorage   $storage,
        User       $user
    ) {
        parent::__construct($dibi, $storage);

        $this->user = $user;
    }

    /**
     * PmManager destructor.
     */
    public function __destruct()
    {
        $this->user = null;

        parent::__destruct();
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
    
    /**
     *
     * @param int $user_id
     *
     * @return bool
     */
    public function deleteByUserFrom($user_id)
    {
        return $this->deleteFluent()
            ->where('[pm_user_id_from] = %i', $user_id)
            ->execute();
    }
    
    /**
     *
     * @param int $user_id
     *
     * @return bool
     */
    public function deleteByUserTo($user_id)
    {
        return $this->deleteFluent()
            ->where('[pm_user_id_to] = %i', $user_id)
            ->execute();
    }
}
