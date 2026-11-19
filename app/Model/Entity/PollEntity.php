<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping\UniqueConstraint;
use App\Model\Repository\PollRepository;
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

#[Entity(repositoryClass: PollRepository::class)]
#[Table(name: 'poll', options: ['comment' => 'Polls'])]
// one poll per topic
#[UniqueConstraint(name: 'UK__Poll__Topic_id', columns: ['topic_id'])]
class PollEntity
{

    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    public int $id;

    #[Column(type: UuidType::NAME, unique: true, options: ['comment' => 'UUID'])]
    public UuidInterface $uuid;

    #[ManyToOne(targetEntity: UserEntity::class, inversedBy: 'polls', cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'User ID'])]
    public UserEntity $user;

    #[ManyToOne(targetEntity: CategoryEntity::class, inversedBy: 'polls', cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'Category ID'])]
    public CategoryEntity $category;

    #[ManyToOne(targetEntity: ForumEntity::class, inversedBy: 'polls', cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'Forum ID'])]
    public ForumEntity $forum;

    #[ManyToOne(targetEntity: TopicEntity::class, inversedBy: 'polls', cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'Topic ID'])]
    public TopicEntity $topic;

    #[Column(type: Types::STRING, length: 1024, nullable: false, options: ['comment' => 'Question'])]
    public string $question;

    // null = open forever
    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => 'Ends at'])]
    public ?DateTimeImmutable $endsAt;

    #[Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => 'Created at'])]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => 'Updated at'])]
    public ?DateTimeImmutable $updatedAt;

    /**
     * @var Collection<int, PollVoteEntity> $votes
     */
    #[OneToMany(targetEntity: PollVoteEntity::class, mappedBy: 'poll', cascade: ['persist', 'remove'])]
    public Collection $votes;

    /**
     * @var Collection<int, PollAnswerEntity> $passwordRequests
     */
    #[OneToMany(targetEntity: PollAnswerEntity::class, mappedBy: 'poll', cascade: ['persist', 'remove'])]
    public Collection $answers;

    public function __construct()
    {
        $this->endsAt = null;
        $this->uuid = Uuid::uuid4();

        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;

        $this->votes = new ArrayCollection();
        $this->answers = new ArrayCollection();
    }

}