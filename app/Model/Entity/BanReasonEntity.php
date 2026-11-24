<?php declare(strict_types=1);

namespace App\Model\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Ramsey\Uuid\Doctrine\UuidType;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * A predefined reason of a ban (slice 7c1): the form of a ban offers it in a select, the ban keeps its own copy of the text.
 */
#[Entity()]
#[Table(name: 'ban_reason', options: ['comment' => 'Predefined reasons of bans'])]
class BanReasonEntity
{
    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    public int $id;

    #[Column(type: UuidType::NAME, unique: true, options: ['comment' => 'UUID'])]
    public UuidInterface $uuid;

    #[Column(type: Types::TEXT, options: ['comment' => 'Text of the reason'])]
    public string $text;

    #[Column(type: Types::INTEGER, options: ['comment' => 'Order'])]
    public int $sortOrder;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, options: ['comment' => 'Created at'])]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true, options: ['comment' => 'Updated at'])]
    public ?DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->sortOrder = 0;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;
    }
}
