<?php declare(strict_types=1);

namespace App\Model\Entity;

use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

class UserForumPermissionEntity
{

    #[ManyToOne(targetEntity: UserEntity::class, inversedBy: 'sessions')]
    #[JoinColumn(nullable: false)]
    public UserEntity $user;


    #[ManyToOne(targetEntity: ForumEntity::class, inversedBy: 'sessions')]
    #[JoinColumn(nullable: false)]
    public ForumEntity $forum;

}