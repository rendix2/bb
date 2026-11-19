<?php declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\Repository\UserForumPermissionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity(repositoryClass: UserForumPermissionRepository::class)]
#[Table(name: 'user_forum')]
class UserForumPermissionEntity
{

    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::INTEGER, unique: true)]
    public int $id;

    #[ManyToOne(targetEntity: UserEntity::class)]
    #[JoinColumn(nullable: false)]
    public UserEntity $user;


    #[ManyToOne(targetEntity: ForumEntity::class)]
    #[JoinColumn(nullable: false)]
    public ForumEntity $forum;

}