<?php declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\Repository\ForumRepository;
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

#[Entity(repositoryClass: ForumRepository::class)]
#[Table(name: 'forum')]
class ForumEntity
{

    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT)]
    public string $id;

    #[Column(type: UuidType::NAME, unique: true)]
    public UuidInterface $uuid;

    #[Column(type: Types::TEXT)]
    public string $name;

    #[Column(type: Types::TEXT)]
    public ?string $description;

    #[ManyToOne(targetEntity: CategoryEntity::class, inversedBy: 'forums')]
    #[JoinColumn(nullable: true)]
    public ?CategoryEntity $category;

    #[ManyToOne(targetEntity: ForumEntity::class, inversedBy: 'forums')]
    #[JoinColumn(nullable: false)]
    public ForumEntity $parent;

    #[Column(type: Types::BOOLEAN)]
    public bool $active;

    //#[Column(type: Types::INTEGER)]
    //public int $topicCount;

    #[Column(type: Types::TEXT, nullable:  true)]
    public string $rules;

    #[Column(type: Types::INTEGER, nullable:  false)]
    public int $sortOrder;

    #[Column(type: Types::DATETIME_IMMUTABLE)]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    public ?DateTimeImmutable $updatedAt;

    /**
     * @var Collection<int, TopicEntity> $topics
     */
    #[OneToMany(targetEntity: TopicEntity::class, mappedBy: 'category', cascade: ['persist', 'remove'])]
    public Collection $topics;

    /**
     * @var Collection<int, PostEntity> $thanks
     */
    #[OneToMany(targetEntity: PostEntity::class, mappedBy: 'forum', cascade: ['persist', 'remove'])]
    public Collection $posts;

    /**
     * @var Collection<int, ThankEntity> $thanks
     */
    #[OneToMany(targetEntity: ThankEntity::class, mappedBy: 'forum', cascade: ['persist', 'remove'])]
    public Collection $thanks;

    /**
     * @var Collection<int, ModeratorUserEntity> $thanks
     */
    #[OneToMany(targetEntity: ModeratorUserEntity::class, mappedBy: 'forum', cascade: ['persist', 'remove'])]
    public Collection $moderatorUsers;



    public int $hasNewPosts = 0;

    public int $hasNewTopics = 0;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();

        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;

        $this->topics = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->thanks = new ArrayCollection();
        $this->moderatorUsers = new ArrayCollection();
    }

}