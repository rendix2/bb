<?php

namespace App\Models\Traits;

use App\Models\Entity\UserEntity;
use App\Models\UsersManager;

/**
 * Description of UsersTrait
 *
 * @author rendix2
 */
trait UsersTrait
{
    /**
     *
     * @var UsersManager $usersManager
     * @inject
     */
    public $usersManager;
        
    /**
     *
     * @param int $user_id
     *
     * @return UserEntity
     */
    public function checkUserParam($user_id)
    {
        if (!isset($user_id)) {
            $this->error('User param is not set.');
        }

        if (!is_numeric($user_id)) {
            $this->error('User param is not numeric.');
        }

        $userDibi = $this->usersManager->getById($user_id);

        if (!$userDibi) {
            $this->error('User was not found.');
        }
        
        return UserEntity::setFromRow($userDibi);
    }
}
