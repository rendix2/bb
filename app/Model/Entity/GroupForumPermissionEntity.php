<?php declare(strict_types=1);

namespace App\Model\Entity;

use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

class GroupForumPermissionEntity
{

    #[ManyToOne(targetEntity: ForumEntity::class, inversedBy: 'sessions')]
    #[JoinColumn(nullable: false)]
    public ForumEntity $forum;


    #[ManyToOne(targetEntity: GroupEntity::class, inversedBy: 'sessions')]
    #[JoinColumn(nullable: false)]
    public GroupEntity $group;

}