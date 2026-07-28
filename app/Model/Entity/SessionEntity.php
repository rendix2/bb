<?php

namespace App\Model\Entity;

use App\Model\Repository\SessionRepository;
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
use Ramsey\Uuid\UuidInterface;

#[Entity(repositoryClass: SessionRepository::class)]
#[Table(name: 'session')]
class SessionEntity
{

    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT)]
    public string $id;

    #[Column(type: UuidType::NAME, unique: true)]
    public UuidInterface $uuid;

    #[Column(type: Types::STRING, length: 512)]
    public string $key;

    #[ManyToOne(targetEntity: UserEntity::class, inversedBy: 'sessions')]
    #[JoinColumn(nullable: false)]
    public UserEntity $user;

    #[Column(type: Types::DATETIME_IMMUTABLE)]
    public DateTimeImmutable $lastActivity;

}