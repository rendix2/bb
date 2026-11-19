<?php

namespace App\Model\Entity;

use App\Model\Repository\UserFavouriteUserRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Ramsey\Uuid\Doctrine\UuidType;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[Entity(repositoryClass: UserFavouriteUserRepository::class)]
#[Table(name: 'user_favourite', options: ['comment' => 'User favourite users'])]
class UserFavouriteUserEntity
{
    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT, unique: true, options: ['comment' => 'ID'])]
    public int $id;

    #[Column(type: UuidType::NAME, unique: true, options: ['comment' => 'UUID'])]
    public UuidInterface $uuid;

    #[ManyToOne(targetEntity: UserEntity::class, cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'User ID'])]
    public UserEntity $user;

    #[ManyToOne(targetEntity: UserEntity::class, cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'Favourite user ID'])]
    public UserEntity $favouriteUser;

    #[Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => 'Created at'])]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => 'Updated at'])]
    public ?DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();

        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;
    }

}
