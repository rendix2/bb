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
use Ramsey\Uuid\Doctrine\UuidType;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * A mass e-mail of the administration (slice 7e4): the history of the sendings. The mails themselves are in the queue
 * (table mail), one per recipient.
 */
#[Entity()]
#[Table(name: 'mass_mail', options: ['comment' => 'Mass e-mails of the administration'])]
class MassMailEntity
{
    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    public int $id;

    #[Column(type: UuidType::NAME, unique: true, options: ['comment' => 'UUID'])]
    public UuidInterface $uuid;

    #[Column(type: Types::STRING, length: 1024, options: ['comment' => 'Subject'])]
    public string $subject;

    #[Column(type: Types::TEXT, options: ['comment' => 'Text (Markdown)'])]
    public string $body;

    // the history stays when the admin is deleted
    #[ManyToOne(targetEntity: UserEntity::class, cascade: ['persist'])]
    #[JoinColumn(nullable: true, onDelete: 'SET NULL', options: ['comment' => 'Sent by (user ID)'])]
    public ?UserEntity $sentBy;

    #[Column(type: Types::INTEGER, options: ['comment' => 'Number of recipients'])]
    public int $recipientCount;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, options: ['comment' => 'Created at'])]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true, options: ['comment' => 'Updated at'])]
    public ?DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->recipientCount = 0;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;
    }
}
