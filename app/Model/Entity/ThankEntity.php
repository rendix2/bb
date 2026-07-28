<?php declare(strict_types=1);

namespace App\Model\Entity;

use App\Database\Types\IpAddressType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity()]
#[Table(name: 'thank')]
class ThankEntity
{
    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT)]
    public string $id;

    #[ManyToOne(targetEntity: CategoryEntity::class, inversedBy: 'XXX')]
    #[JoinColumn(nullable: false)]
    public CategoryEntity $category;

    #[ManyToOne(targetEntity: ForumEntity::class, inversedBy: 'XXX')]
    #[JoinColumn(nullable: false)]
    public ForumEntity $forum;

    #[ManyToOne(targetEntity: TopicEntity::class, inversedBy: 'XXX')]
    #[JoinColumn(nullable: false)]
    public TopicEntity $topic;

    #[ManyToOne(targetEntity: PostEntity::class, inversedBy: 'XXXX')]
    #[JoinColumn(nullable: false)]
    public PostEntity $post;

    #[ManyToOne(targetEntity: UserEntity::class, inversedBy: 'XXXX')]
    #[JoinColumn(nullable: false)]
    public UserEntity $user;

    #[Column(type: IpAddressType::NAME, nullable: false)]
    public string $ipAddress;

}