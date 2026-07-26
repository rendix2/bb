<?php declare(strict_types=1);

namespace App\Model\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity()]
#[Table(name: 'topic_watch')]
class TopicWatchEntity
{

    #[Id()]
    #[ManyToOne(targetEntity: UserEntity::class, inversedBy: 'XXX')]
    #[JoinColumn(nullable: false)]
    public UserEntity $user;

    #[Id()]
    #[ManyToOne(targetEntity: TopicEntity::class, inversedBy: 'YYY')]
    #[JoinColumn(nullable: false)]
    public TopicEntity $topic;
}