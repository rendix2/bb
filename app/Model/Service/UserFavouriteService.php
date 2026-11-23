<?php declare(strict_types=1);

namespace App\Model\Service;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\UserEntity;
use App\Model\Entity\UserFavouriteUserEntity;
use App\Model\Repository\UserFavouriteUserRepository;

/**
 * The favourite users of a user (slice 4f): private (decision of 2026-11-19), the list sees only its owner. A user is never his own
 * favourite and every other user is one only once (unique index user_id + favourite_user_id).
 */
class UserFavouriteService
{
    public const string ERROR_SELF = 'web-user-view.favourite.self';

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly UserFavouriteUserRepository $repository,
    ) {
    }

    /**
     * Is $favourite a favourite of $user? (The old code asked whether the user was his own favourite.)
     */
    public function isFavourite(UserEntity $user, UserEntity $favourite) : bool
    {
        return $this->find($user, $favourite) !== null;
    }

    /**
     * @return list<UserFavouriteUserEntity>
     */
    public function favouritesOf(UserEntity $user) : array
    {
        return $this->repository->favouritesOf($user);
    }

    public function add(UserEntity $user, UserEntity $favourite) : void
    {
        if ($user->id === $favourite->id) {
            throw new UserFavouriteException(self::ERROR_SELF);
        }

        if ($this->find($user, $favourite) !== null) {
            return;
        }

        $entity = new UserFavouriteUserEntity();
        $entity->user = $user;
        $entity->favouriteUser = $favourite;
        $this->em->persist($entity);
        $this->em->flush();
    }

    public function remove(UserEntity $user, UserEntity $favourite) : void
    {
        $entity = $this->find($user, $favourite);

        if ($entity !== null) {
            $this->em->remove($entity);
            $this->em->flush();
        }
    }

    private function find(UserEntity $user, UserEntity $favourite) : ?UserFavouriteUserEntity
    {
        return $this->repository->findOneBy(['user' => $user, 'favouriteUser' => $favourite]);
    }
}
