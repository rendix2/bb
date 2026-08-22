<?php declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\Repository\GroupForumPermissionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity(repositoryClass: GroupForumPermissionRepository::class)]
#[Table(name: 'permission_group_forum')]
class GroupForumPermissionEntity
{

    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::INTEGER, unique: true)]
    public int $id;

    #[ManyToOne(targetEntity: ForumEntity::class, inversedBy: 'sessions')]
    #[JoinColumn(nullable: false)]
    public ForumEntity $forum;


    #[ManyToOne(targetEntity: GroupEntity::class, inversedBy: 'sessions')]
    #[JoinColumn(nullable: false)]
    public GroupEntity $group;

}