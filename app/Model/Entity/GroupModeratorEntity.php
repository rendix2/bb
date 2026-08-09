<?php declare(strict_types=1);

namespace App\Model\Entity;

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

#[Entity()]
#[Table(name: 'group_moderator')]
class GroupModeratorEntity
{

    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT)]
    public string $id;

    #[Column(type: UuidType::NAME, unique: true)]
    public UuidInterface $uuid;

    #[ManyToOne(targetEntity: ForumEntity::class, inversedBy: 'sessions')]
    #[JoinColumn(nullable: false)]
    public ForumEntity $forum;


    #[ManyToOne(targetEntity: GroupEntity::class, inversedBy: 'sessions')]
    #[JoinColumn(nullable: false)]
    public GroupEntity $group;


}