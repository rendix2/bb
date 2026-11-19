<?php declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\Repository\GroupModeratorRepository;
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

/**
 * A group whose members moderate a forum.
 */
#[Entity(repositoryClass: GroupModeratorRepository::class)]
#[Table(name: 'group_moderator', options: ['comment' => 'Groups moderating forums'])]
class GroupModeratorEntity
{

    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    public int $id;

    #[Column(type: UuidType::NAME, unique: true, options: ['comment' => 'UUID'])]
    public UuidInterface $uuid;

    #[ManyToOne(targetEntity: GroupEntity::class, cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'Group ID'])]
    public GroupEntity $group;

    #[ManyToOne(targetEntity: ForumEntity::class, cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'Forum ID'])]
    public ForumEntity $forum;

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
