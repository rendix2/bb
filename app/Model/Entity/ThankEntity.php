<?php declare(strict_types=1);

namespace App\Model\Entity;

use App\Database\Types\IpAddressType;
use App\Model\Repository\ThankRepository;
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

#[Entity(repositoryClass: ThankRepository::class)]
#[Table(name: 'thank')]
class ThankEntity
{
    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT)]
    public string $id;

    #[Column(type: UuidType::NAME, unique: true)]
    public UuidInterface $uuid;

    #[ManyToOne(targetEntity: CategoryEntity::class, inversedBy: 'thanks')]
    #[JoinColumn(nullable: false)]
    public CategoryEntity $category;

    #[ManyToOne(targetEntity: ForumEntity::class, inversedBy: 'thanks')]
    #[JoinColumn(nullable: false)]
    public ForumEntity $forum;

    #[ManyToOne(targetEntity: TopicEntity::class, inversedBy: 'thanks')]
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

    #[Column(type: Types::DATETIME_IMMUTABLE)]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    public ?DateTimeImmutable $updatedAt;

}