<?php

namespace App\Model\Repository;

use App\Model\Entity\UserEntity;
use App\Model\Entity\UserFavouriteUserEntity;
use Doctrine\ORM\EntityRepository;
use Nette\Security\User;

/**
 * class UserRepository
 *
 * @package App\Model\Repository
 * @extends EntityRepository<UserFavouriteUserEntity>
 */
class UserFavouriteUserRepository extends EntityRepository
{

    public function isFavourited(User $user, UserEntity $favouriteUser): ?UserFavouriteUserEntity
    {
        return $this->findOneBy(
            [
                'user' => $user->getId(),
                'favouriteUser' => $favouriteUser->id,
            ]
        );
    }

}