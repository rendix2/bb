<?php declare(strict_types=1);

namespace App\Model\Entity;

use App\Database\Types\IpAddressType;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Ramsey\Uuid\Doctrine\UuidType;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[Entity()]
#[Table(name: 'report', options: ['comment' => 'Post reports'])]
// one report per user and post
#[UniqueConstraint(name: 'UK__Report__User_id_post_id', columns: ['user_id', 'post_id'])]
class ReportEntity
{

    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    public int $id;

    #[Column(type: UuidType::NAME, unique: true, options: ['comment' => 'UUID'])]
    public UuidInterface $uuid;

    #[ManyToOne(targetEntity: CategoryEntity::class, cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'Category ID'])]
    public CategoryEntity $category;

    #[ManyToOne(targetEntity: ForumEntity::class, cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'Forum ID'])]
    public ForumEntity $forum;

    #[ManyToOne(targetEntity: TopicEntity::class, cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'Topic ID'])]
    public TopicEntity $topic;

    #[ManyToOne(targetEntity: PostEntity::class, cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'Post ID'])]
    public PostEntity $post;

    #[ManyToOne(targetEntity: UserEntity::class, cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'User ID'])]
    public UserEntity $user;

    #[Column(type: IpAddressType::NAME, options: ['comment' => 'IP Address'])]
    public string $ipAddress;

    #[Column(type: Types::TEXT, nullable: false, options: ['comment' => 'Report text'])]
    public string $reportText;

    #[Column(type: Types::INTEGER, nullable: false, options: ['comment' => 'Status (0 = open)'])]
    public int $status;

    #[Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => 'Created at'])]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => 'Updated at'])]
    public ?DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();

        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;
    }

}