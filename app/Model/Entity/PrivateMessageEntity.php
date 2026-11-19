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

/**
 * One message of a private message thread, written by its author.
 */
#[Entity()]
#[Table(name: 'private_message', options: ['comment' => 'Private messages'])]
class PrivateMessageEntity
{

    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    public int $id;

    #[ManyToOne(targetEntity: PrivateMessageThreadEntity::class, inversedBy: 'messages', cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'Private message Thread ID'])]
    public PrivateMessageThreadEntity $thread;

    #[ManyToOne(targetEntity: UserEntity::class, cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'Author User ID'])]
    public UserEntity $user;

    #[Column(type: Types::TEXT, options: ['comment' => 'Text'])]
    public string $text;

    #[Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => 'Created at'])]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => 'Updated at'])]
    public ?DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;
    }

}
