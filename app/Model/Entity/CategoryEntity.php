<?php declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\Repository\CategoryRepository;
use DateTimeImmutable;
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
use Ramsey\Uuid\UuidInterface;

#[Entity(repositoryClass: CategoryRepository::class)]
#[Table(name: 'category')]
class CategoryEntity
{

    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT)]
    public string $id;

    #[Column(type: UuidType::NAME, unique: true)]
    public UuidInterface $uuid;

    #[ManyToOne(targetEntity: CategoryEntity::class, inversedBy: 'children')]
    #[JoinColumn(nullable: false)]
    public CategoryEntity $parent;

    #[Column(type: Types::TEXT)]
    public string $name;

    #[Column(type: Types::INTEGER)]
    public int $order;

    #[Column(type: Types::BOOLEAN)]
    public bool $active;

    #[Column(type: Types::DATETIME_IMMUTABLE)]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    public ?DateTimeImmutable $updatedAt;

    /**
     * @var Collection<int, CategoryEntity> $children
     */
    #[OneToMany(targetEntity: CategoryEntity::class, mappedBy: 'parent', cascade: ['persist', 'remove'])]
    public Collection $children;

    /**
     * @var Collection<int, ForumEntity> $forums
     */
    #[OneToMany(targetEntity: ForumEntity::class, mappedBy: 'category', cascade: ['persist', 'remove'])]
    public Collection $forums;

    /**
     * @var Collection<int, ThankEntity> $thanks
     */
    #[OneToMany(targetEntity: ThankEntity::class, mappedBy: 'category', cascade: ['persist', 'remove'])]
    public Collection $thanks;

}