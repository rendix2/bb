<?php declare(strict_types=1);

namespace App\Model\Entity;

use Doctrine\ORM\Mapping\Index;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

#[Entity()]
#[Table(name: 'mail', options: ['comment' => 'Mail queue and history'])]
#[Index(name: 'K__Mail__Sent_at', columns: ['sent_at'])]
class MailEntity
{

    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    public int $id;

    #[Column(type: Types::STRING, nullable: false, options: ['comment' => 'Email to'])]
    public string $emailTo;

    //#[ManyToOne(targetEntity: UserEmailEntity::class)]
    //#[JoinColumn('emailTo', unique: true, nullable: false)]
    //public UserEmailEntity $userEmail;

    #[Column(type: Types::STRING, length: 1024, nullable: false, options: ['comment' => 'Subject'])]
    public string $subject;

    #[Column(type: Types::TEXT, nullable: false, options: ['comment' => 'Body'])]
    public string $body;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, options: ['comment' => 'Created at'])]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true, options: ['comment' => 'Updated at'])]
    public ?DateTimeImmutable $updatedAt;

    // null = waits in the queue (sent by the console command), else when it was sent
    #[Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true, options: ['comment' => 'Sent at'])]
    public ?DateTimeImmutable $sentAt;

    public function __construct()
    {
        $this->sentAt = null;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;
    }

}
