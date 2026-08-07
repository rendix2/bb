<?php

namespace App\Model\Repository;

use App\Model\Entity\UserEntity;
use Doctrine\ORM\EntityRepository;
use Nette\Security\User;

/**
 * class UserRepository
 *
 * @package App\Model\Repository
 * @extends EntityRepository<UserEntity>
 */
class UserRepository extends EntityRepository
{

    public function findOneById(string $id) : ?UserEntity
    {
        return $this
            ->findOneBy(
                [
                    'id' => $id,
                ]
            );
    }

    public function findOneByUuid(string $uuid) : ?UserEntity
    {
        return $this
            ->findOneBy(
                [
                    'uuid' => $uuid,
                ]
            );
    }

    public function findOneByEmail(string $email) : ?UserEntity
    {
        return $this
            ->findOneBy(
                [
                    'email' => $email,
                ]
            );
    }

    public function findByEmails(array $emails): array
    {
        return $this
            ->findBy(
                [
                    'email' => $emails,
                ]
            );
    }

    public function findOneByUsername(string $username): ?UserEntity
    {
        return $this
            ->findOneBy(
                [
                    'username' => $username,
                ]
            );
    }

    /**
     * @return UserEntity[]
     */
    public function findWithAvatar() : array
    {
        return $this
            ->createQueryBuilder('_u')
            ->where('_u.avatar IS NOT NULL')

            ->getQuery()
            ->getResult();
    }

    public function findOneByNetteUser(User $user): ?UserEntity
    {
        return $this->findOneBy(
            [
                'id' => $user->getId(),
            ]
        );
    }



}