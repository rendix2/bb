<?php declare(strict_types=1);

namespace App\Model\Entity;

use Doctrine\ORM\Mapping\UniqueConstraint;
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
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[Entity(repositoryClass: ThankRepository::class)]
#[Table(name: 'thank', options: ['comment' => 'Thanks'])]
// a thank of a topic has no post: once per user and post, once per user and topic (the partial index)
#[UniqueConstraint(name: 'UK__Thank__User_id_post_id', columns: ['user_id', 'post_id'])]
#[UniqueConstraint(name: 'UK__Thank__User_id_topic_id', columns: ['user_id', 'topic_id'], options: ['where' => '(post_id IS NULL)'])]
class ThankEntity
{
    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    public int $id;

    #[Column(type: UuidType::NAME, unique: true, options: ['comment' => 'UUID'])]
    public UuidInterface $uuid;

    #[ManyToOne(targetEntity: TopicEntity::class, inversedBy: 'thanks', cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'Topic ID'])]
    public TopicEntity $topic;

    #[ManyToOne(targetEntity: PostEntity::class, cascade: ['persist'])]
    #[JoinColumn(nullable: true, onDelete: 'CASCADE', options: ['comment' => 'Post ID (null = thanks for the topic)'])]
    public ?PostEntity $post;

    #[ManyToOne(targetEntity: UserEntity::class, inversedBy: 'thanks', cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'User ID'])]
    public UserEntity $user;

    #[Column(type: IpAddressType::NAME, nullable: false, options: ['comment' => 'IP Address'])]
    public string $ipAddress;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, options: ['comment' => 'Created at'])]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true, options: ['comment' => 'Updated at'])]
    public ?DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();

        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;
    }

}