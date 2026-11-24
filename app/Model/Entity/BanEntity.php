<?php declare(strict_types=1);

namespace App\Model\Entity;

use App\Database\Types\IpAddressType;
use App\Model\Enum\BanScope;
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
 * A ban (slice 7c1) of a user, an e-mail or an IP address (at least one: CHECK CH__Ban__Target), of the scope chosen by the
 * admin, until expires_at (null = for ever). Enforced by BanService.
 */
#[Entity()]
#[Table(name: 'ban', options: ['comment' => 'Bans'])]
class BanEntity
{
    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    public int $id;

    #[Column(type: UuidType::NAME, unique: true, options: ['comment' => 'UUID'])]
    public UuidInterface $uuid;

    #[ManyToOne(targetEntity: UserEntity::class, cascade: ['persist'])]
    #[JoinColumn(nullable: true, onDelete: 'CASCADE', options: ['comment' => 'Banned user ID'])]
    public ?UserEntity $user;

    // compared regardless of the case
    #[Column(type: Types::STRING, length: 512, nullable: true, options: ['comment' => 'Banned e-mail'])]
    public ?string $email;

    #[Column(type: IpAddressType::NAME, nullable: true, options: ['comment' => 'Banned IP address'])]
    public ?string $ipAddress;

    #[Column(type: Types::STRING, length: 16, enumType: BanScope::class, options: ['comment' => 'What the ban blocks (BanScope)'])]
    public BanScope $scope;

    #[Column(type: Types::TEXT, options: ['comment' => 'Reason shown to the banned visitor'])]
    public string $reason;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true, options: ['comment' => 'Expires at (null = for ever)'])]
    public ?DateTimeImmutable $expiresAt;

    // the admin who banned; the ban stays when his account is deleted
    #[ManyToOne(targetEntity: UserEntity::class, cascade: ['persist'])]
    #[JoinColumn(nullable: true, onDelete: 'SET NULL', options: ['comment' => 'Banned by (user ID)'])]
    public ?UserEntity $createdBy;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, options: ['comment' => 'Created at'])]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true, options: ['comment' => 'Updated at'])]
    public ?DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->user = null;
        $this->email = null;
        $this->ipAddress = null;
        $this->scope = BanScope::Web;
        $this->expiresAt = null;
        $this->createdBy = null;

        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;
    }

    public function isActive(DateTimeImmutable $now) : bool
    {
        return $this->expiresAt === null || $this->expiresAt > $now;
    }
}
