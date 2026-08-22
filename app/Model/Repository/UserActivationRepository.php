<?php

namespace App\Model\Repository;

use App\Model\Entity\UserActivationEntity;
use Doctrine\ORM\EntityRepository;


/**
 * class UserActivationRepository
 *
 * @package App\Model\Repository
 * @extends EntityRepository<UserActivationEntity>
 */
class UserActivationRepository extends EntityRepository
{

    public function findForActivation(int $userId, string $key): ?UserActivationEntity
    {
        return $this->createQueryBuilder('_ua')

            ->addSelect('_u')
            ->innerJoin('_ua.user', '_u')

            ->where('_ua.user = :userId')
            ->setParameter('userId', $userId)

            ->andWhere('_ua.activationKey = :key')
            ->setParameter('key', $key)

            ->andWhere('_u.isActive = :active')
            ->setParameter('active', false)

            ->getQuery()
            ->getOneOrNullResult();
    }

}