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
#[Table(name: 'topic')]
class TopicEntity
{

    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT)]
    public string $id;

    #[Column(type: UuidType::NAME, unique: true)]
    public UuidInterface $uuid;

    #[ManyToOne(targetEntity: CategoryEntity::class, inversedBy: 'topics')]
    #[JoinColumn(nullable: false)]
    public CategoryEntity $category;

    #[ManyToOne(targetEntity: ForumEntity::class, inversedBy: 'topics')]
    #[JoinColumn(nullable: false)]
    public ForumEntity $forum;

    #[ManyToOne(targetEntity: UserEntity::class, inversedBy: 'topics')]
    #[JoinColumn(nullable: false)]
    public UserEntity $user;

    #[ManyToOne(targetEntity: PostEntity::class, inversedBy: 'firstInTopics')]
    #[JoinColumn(nullable: true)]
    public ?PostEntity $firstPost;

    #[ManyToOne(targetEntity: PostEntity::class, inversedBy: 'lastInTopics')]
    #[JoinColumn(nullable: true)]
    public ?PostEntity $lastPost;

    #[ManyToOne(targetEntity: PollEntity::class, inversedBy: 'topicsWithPoll')]
    #[JoinColumn(nullable: true)]
    public ?PollEntity $poll;

    #[Column(type: Types::STRING, length: 1024, nullable: false)]
    public string $name;

    #[Column(type: Types::DATETIME_IMMUTABLE)]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    public ?DateTimeImmutable $updatedAt;

    /**
     * @var Collection<int, TopicEntity> $posts
     */
    #[OneToMany(targetEntity: TopicEntity::class, mappedBy: 'topic', cascade: ['persist', 'remove'])]
    public Collection $topics;

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

        $this->topics = new ArrayCollection();
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