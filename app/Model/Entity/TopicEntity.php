<?php declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\Repository\TopicRepository;
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

#[Entity(repositoryClass: TopicRepository::class)]
#[Table(name: 'topic', options: ['comment' => 'Topics'])]
class TopicEntity
{

    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    public int $id;

    #[Column(type: UuidType::NAME, unique: true, options: ['comment' => 'UUID'])]
    public UuidInterface $uuid;

    #[ManyToOne(targetEntity: CategoryEntity::class, inversedBy: 'topics', cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'Category ID'])]
    public CategoryEntity $category;

    #[ManyToOne(targetEntity: ForumEntity::class, inversedBy: 'topics', cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'Forum ID'])]
    public ForumEntity $forum;

    #[ManyToOne(targetEntity: UserEntity::class, inversedBy: 'topics', cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'User ID'])]
    public UserEntity $user;

    #[Column(type: Types::STRING, length: 1024, nullable: false, options: ['comment' => 'Name'])]
    public string $name;

    #[Column(type: Types::BOOLEAN, options: ['comment' => 'Is locked?'])]
    public bool $isLocked;

    #[Column(type: Types::INTEGER, options: ['comment' => 'View count'])]
    public int $viewCount;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, options: ['comment' => 'Created at'])]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true, options: ['comment' => 'Updated at'])]
    public ?DateTimeImmutable $updatedAt;

    /**
     * @var Collection<int, PostEntity> $posts
     */
    #[OneToMany(targetEntity: PostEntity::class, mappedBy: 'topic', cascade: ['persist', 'remove'])]
    public Collection $posts;

    /**
     * @var Collection<int, ThankEntity> $thanks
     */
    #[OneToMany(targetEntity: ThankEntity::class, mappedBy: 'topic', cascade: ['persist', 'remove'])]
    public Collection $thanks;

    /**
     * @var Collection<int, PollEntity> $polls
     */
    #[OneToMany(targetEntity: PollEntity::class, mappedBy: 'topic', cascade: ['persist', 'remove'])]
    public Collection $polls;

    /**
     * @var Collection<int, TopicWatchEntity> $watches
     */
    #[OneToMany(targetEntity: TopicWatchEntity::class, mappedBy: 'topic', cascade: ['persist', 'remove'])]
    public Collection $watches;

    /**
     * @var Collection<int, PollAnswerEntity> $pollAnswers
     */
    #[OneToMany(targetEntity: PollAnswerEntity::class, mappedBy: 'topic', cascade: ['persist', 'remove'])]
    public Collection $pollAnswers;

    /**
     * @var Collection<int, PollVoteEntity> $pollVotes
     */
    #[OneToMany(targetEntity: PollVoteEntity::class, mappedBy: 'topic', cascade: ['persist', 'remove'])]
    public Collection $pollVotes;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->isLocked = false;
        $this->viewCount = 0;

        $this->posts = new ArrayCollection();
        $this->thanks = new ArrayCollection();
        $this->polls = new ArrayCollection();

        $this->watches = new ArrayCollection();
        $this->pollAnswers = new ArrayCollection();
        $this->pollVotes = new ArrayCollection();

        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;
    }

}