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
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

/**
 * A conversation of private messages between its participants.
 */
#[Entity()]
#[Table(name: 'private_message_thread', options: ['comment' => 'Private message threads'])]
class PrivateMessageThreadEntity
{

    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    public int $id;

    #[Column(type: Types::TEXT, options: ['comment' => 'Subject'])]
    public string $subject;

    #[Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => 'Created at'])]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => 'Updated at'])]
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
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;

        $this->messages = new ArrayCollection();
        $this->participants = new ArrayCollection();
    }

}
