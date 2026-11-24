<?php declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\Repository\RankRepository;
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

#[Entity(repositoryClass: RankRepository::class)]
#[Table(name: 'rank', options: ['comment' => 'Ranks'])]
class RankEntity
{
    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    public int $id;

    #[Column(type: UuidType::NAME, unique: true, options: ['comment' => 'UUID'])]
    public UuidInterface $uuid;

    #[Column(type: Types::STRING, length: 512, options: ['comment' => 'Name'])]
    public string $name;

    // the rank of a user with at least this many posts; null for a special rank (given by the admin)
    #[Column(type: Types::INTEGER, nullable: true, options: ['comment' => 'Minimum posts'])]
    public ?int $minPosts;

    #[Column(type: Types::TEXT, unique: true, options: ['comment' => 'File name'])]
    public string $fileName;

    #[Column(type: Types::BOOLEAN, options: ['comment' => 'Is special'])]
    public bool $isSpecial;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, options: ['comment' => 'Created at'])]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true, options: ['comment' => 'Updated at'])]
    public ?DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();

        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;
    }

}