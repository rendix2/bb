<?php declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\Repository\GroupForumPermissionRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
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

/**
 * Access of a group to a forum.
 */
#[Entity(repositoryClass: GroupForumPermissionRepository::class)]
#[Table(name: 'group_forum', options: ['comment' => 'Forum access of groups'])]
// one row per pair
#[UniqueConstraint(name: 'UK__Group_forum__Group_id_forum_id', columns: ['group_id', 'forum_id'])]
class GroupForumPermissionEntity
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

    // the flag columns without a prefix (topic_add, post_add, ...)
    #[Embedded(class: ForumPermissions::class, columnPrefix: false)]
    public ForumPermissions $permissions;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, options: ['comment' => 'Created at'])]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true, options: ['comment' => 'Updated at'])]
    public ?DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->permissions = new ForumPermissions();
        $this->uuid = Uuid::uuid4();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;
    }

}
