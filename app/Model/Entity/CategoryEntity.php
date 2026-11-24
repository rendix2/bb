<?php declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\Repository\CategoryRepository;
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

#[Entity(repositoryClass: CategoryRepository::class)]
#[Table(name: 'category', options: ['comment' => 'Categories'])]
class CategoryEntity
{

    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    public int $id;

    #[Column(type: UuidType::NAME, unique: true, options: ['comment' => 'UUID'])]
    public UuidInterface $uuid;

    #[ManyToOne(targetEntity: CategoryEntity::class, inversedBy: 'children', cascade: ['persist'])]
    #[JoinColumn(nullable: true, options: ['comment' => 'Parent ID'])]
    public ?CategoryEntity $parent;

    #[Column(type: Types::TEXT, options: ['comment' => 'Name'])]
    public string $name;

    #[Column(type: Types::INTEGER, nullable: false, options: ['comment' => 'Order'])]
    public int $sortOrder;

    #[Column(type: Types::BOOLEAN, options: ['comment' => 'Active'])]
    public bool $active;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, options: ['comment' => 'Created at'])]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true, options: ['comment' => 'Updated at'])]
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
     * @var Collection<int, TopicEntity> $posts
     */
    #[OneToMany(targetEntity: TopicEntity::class, mappedBy: 'category', cascade: ['persist', 'remove'])]
    public Collection $topics;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();

        $this->children = new ArrayCollection();
        $this->topics = new ArrayCollection();
        $this->forums = new ArrayCollection();


        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;
    }

}