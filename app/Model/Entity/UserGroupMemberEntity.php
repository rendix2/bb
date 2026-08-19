<?php declare(strict_types=1);

namespace App\Model\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity(repositoryClass: null)]
#[Table(name: 'user_group_member')]
class UserGroupMemberEntity
{

    #[ManyToOne(targetEntity: UserEntity::class, inversedBy: 'sessions')]
    #[JoinColumn(nullable: false)]
    public UserEntity $user;


    #[ManyToOne(targetEntity: GroupEntity::class, inversedBy: 'sessions')]
    #[JoinColumn(nullable: false)]
    public GroupEntity $group;

}