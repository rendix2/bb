<?php declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\Repository\ForumRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
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
#[Table(name: 'forum', options: ['comment' => 'Forums'])]
class ForumEntity
{

    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    public int $id;

    #[Column(type: UuidType::NAME, unique: true, options: ['comment' => 'UUID'])]
    public UuidInterface $uuid;

    #[Column(type: Types::TEXT, options: ['comment' => 'Name'])]
    public string $name;

    #[Column(type: Types::TEXT, nullable: true, options: ['comment' => 'Description'])]
    public ?string $description;

    #[ManyToOne(targetEntity: CategoryEntity::class, inversedBy: 'forums', cascade: ['persist'])]
    #[JoinColumn(nullable: true, options: ['comment' => 'Category ID'])]
    public ?CategoryEntity $category;

    #[ManyToOne(targetEntity: ForumEntity::class, inversedBy: 'children', cascade: ['persist'])]
    #[JoinColumn(nullable: true, options: ['comment' => 'Parent ID'])]
    public ?ForumEntity $parent;

    #[Column(type: Types::BOOLEAN, options: ['comment' => 'Active'])]
    public bool $active;

    //#[Column(type: Types::INTEGER)]
    //public int $topicCount;

    #[Column(type: Types::TEXT, nullable:  true, options: ['comment' => 'Rules'])]
    public ?string $rules;

    #[Column(type: Types::INTEGER, nullable:  false, options: ['comment' => 'Order'])]
    public int $sortOrder;

    // the flag columns without a prefix (topic_add, post_add, ...)
    #[Embedded(class: ForumPermissions::class, columnPrefix: false)]
    public ForumPermissions $permissions;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, options: ['comment' => 'Created at'])]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true, options: ['comment' => 'Updated at'])]
    public ?DateTimeImmutable $updatedAt;

    /**
     * @var Collection<int, TopicEntity> $topics
     */
    #[OneToMany(targetEntity: TopicEntity::class, mappedBy: 'forum', cascade: ['persist', 'remove'])]
    public Collection $topics;

    /**
     * @var Collection<int, UserModeratorEntity> $thanks
     */
    #[OneToMany(targetEntity: UserModeratorEntity::class, mappedBy: 'forum', cascade: ['persist', 'remove'])]
    public Collection $moderatorUsers;

    /**
     * @var Collection<int, ForumEntity> $thanks
     */
    #[OneToMany(targetEntity: ForumEntity::class, mappedBy: 'parent')]
    public Collection $children;



    public int $hasNewPosts = 0;

    public int $hasNewTopics = 0;

    public function __construct()
    {
        $this->permissions = new ForumPermissions();
        // defaults of logged-in users in this forum
        $this->permissions->grantDefaultsOfANewForum();
        $this->uuid = Uuid::uuid4();

        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;

        $this->topics = new ArrayCollection();
        $this->moderatorUsers = new ArrayCollection();
        $this->children = new ArrayCollection();

    }

}