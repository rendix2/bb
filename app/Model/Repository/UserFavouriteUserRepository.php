<?php declare(strict_types=1);

namespace App\Model\Repository;

use App\Model\Entity\UserEntity;
use App\Model\Entity\UserFavouriteUserEntity;
use Doctrine\ORM\EntityRepository;

/**
 * class UserFavouriteUserRepository
 *
 * @package App\Model\Repository
 * @extends EntityRepository<UserFavouriteUserEntity>
 */
class UserFavouriteUserRepository extends EntityRepository
{
    /**
     * The favourite users of the user (with them loaded), by username.
     *
     * @return list<UserFavouriteUserEntity>
     */
    public function favouritesOf(UserEntity $user) : array
    {
        /** @var list<UserFavouriteUserEntity> */
        return $this->createQueryBuilder('_f')
            ->addSelect('_u')
            ->join('_f.favouriteUser', '_u')
            ->where('_f.user = :user')
            ->setParameter('user', $user)
            ->orderBy('_u.username', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
