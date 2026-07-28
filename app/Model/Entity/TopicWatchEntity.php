<?php declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\Repository\TopicWatchRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Ramsey\Uuid\Doctrine\UuidType;
use Ramsey\Uuid\UuidInterface;

#[Entity(TopicWatchRepository::class)]
#[Table(name: 'topic_watch')]
class TopicWatchEntity
{


    #[Column(type: UuidType::NAME, unique: true)]
    public UuidInterface $uuid;

    #[Id()]
    #[ManyToOne(targetEntity: UserEntity::class, inversedBy: 'XXX')]
    #[JoinColumn(nullable: false)]
    public UserEntity $user;

    #[Id()]
    #[ManyToOne(targetEntity: TopicEntity::class, inversedBy: 'YYY')]
    #[JoinColumn(nullable: false)]
    public TopicEntity $topic;
}