<?php declare(strict_types=1);

namespace App\Model\Entity;

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

/**
 * A user taking part in a private message thread, with his own read / deleted / starred state; once per thread.
 * is_deleted = he left the thread (hidden for him, no new messages, no mails); the founder may add him again.
 */
#[Entity()]
#[Table(name: 'private_message_participant', options: ['comment' => 'Private message participants'])]
#[UniqueConstraint(name: 'UK__Private_message_participant__User_id_thread_id', columns: ['user_id', 'thread_id'])]
class PrivateMessageParticipantEntity
{

    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    public int $id;

    #[ManyToOne(targetEntity: PrivateMessageThreadEntity::class, inversedBy: 'participants', cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'Private message Thread ID'])]
    public PrivateMessageThreadEntity $thread;

    #[ManyToOne(targetEntity: UserEntity::class, cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'User ID'])]
    public UserEntity $user;

    #[Column(type: Types::BOOLEAN, options: ['comment' => 'Is read'])]
    public bool $isRead;

    #[Column(type: Types::BOOLEAN, options: ['comment' => 'Is deleted'])]
    public bool $isDeleted;

    #[Column(type: Types::BOOLEAN, options: ['comment' => 'Is starred'])]
    public bool $isStarred;

    // the participant wants a mail about a new message (his choice per thread, like watching a topic)
    #[Column(type: Types::BOOLEAN, options: ['comment' => 'Wants a mail about a new message?'])]
    public bool $wantsMail;

    // a mail about a new message was queued; reset when he opens the thread again (one mail until the next visit)
    #[Column(type: Types::BOOLEAN, options: ['comment' => 'Is notified?'])]
    public bool $isNotified;

    #[Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => 'Created at'])]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => 'Updated at'])]
    public ?DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->isRead = false;
        $this->isDeleted = false;
        $this->isStarred = false;
        $this->wantsMail = false;
        $this->isNotified = false;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;
    }

}
