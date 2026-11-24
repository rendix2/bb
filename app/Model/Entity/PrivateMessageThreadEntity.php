<?php declare(strict_types=1);

namespace App\Model\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Ramsey\Uuid\Doctrine\UuidType;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * A conversation of private messages between its participants (two or more, decision of 2026-11-24); in the address by uuid.
 * The founder adds participants; when he is deleted the thread stays for the others (SET NULL), nobody can add then.
 */
#[Entity()]
#[Table(name: 'private_message_thread', options: ['comment' => 'Private message threads'])]
class PrivateMessageThreadEntity
{

    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    public int $id;

    #[Column(type: UuidType::NAME, unique: true, options: ['comment' => 'UUID'])]
    public UuidInterface $uuid;

    #[ManyToOne(targetEntity: UserEntity::class, cascade: ['persist'])]
    #[JoinColumn(nullable: true, onDelete: 'SET NULL', options: ['comment' => 'Founder User ID'])]
    public ?UserEntity $user;

    #[Column(type: Types::TEXT, options: ['comment' => 'Subject'])]
    public string $subject;

    // the list of threads is ordered by it (the newest message first)
    #[Column(type: Types::DATETIMETZ_IMMUTABLE, options: ['comment' => 'Last message at'])]
    public DateTimeImmutable $lastMessageAt;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, options: ['comment' => 'Created at'])]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true, options: ['comment' => 'Updated at'])]
    public ?DateTimeImmutable $updatedAt;

    /**
     * @var Collection<int, PrivateMessageEntity>
     */
    #[OneToMany(targetEntity: PrivateMessageEntity::class, mappedBy: 'thread')]
    public Collection $messages;

    /**
     * @var Collection<int, PrivateMessageParticipantEntity>
     */
    #[OneToMany(targetEntity: PrivateMessageParticipantEntity::class, mappedBy: 'thread')]
    public Collection $participants;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->user = null;
        $this->createdAt = new DateTimeImmutable();
        $this->lastMessageAt = $this->createdAt;
        $this->updatedAt = null;

        $this->messages = new ArrayCollection();
        $this->participants = new ArrayCollection();
    }

}
