<?php

namespace App\Model\Entity;

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
#[Table(name: 'poll')]
class PollEntity
{

    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT)]
    public string $id;

    #[Column(type: UuidType::NAME, unique: true)]
    public UuidInterface $uuid;

    #[ManyToOne(targetEntity: UserEntity::class, inversedBy: 'polls')]
    #[JoinColumn(nullable: false)]
    public UserEntity $user;

    #[ManyToOne(targetEntity: CategoryEntity::class, inversedBy: 'polls')]
    #[JoinColumn(nullable: false)]
    public CategoryEntity $category;

    #[ManyToOne(targetEntity: ForumEntity::class, inversedBy: 'polls')]
    #[JoinColumn(nullable: false)]
    public ForumEntity $forum;

    #[ManyToOne(targetEntity: TopicEntity::class, inversedBy: 'polls')]
    #[JoinColumn(nullable: false)]
    public TopicEntity $topic;

    #[Column(type: Types::STRING, length: 1024, nullable: false)]
    public string $question;

    #[Column(type: Types::DATETIME_IMMUTABLE)]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
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

    /**
     * @var Collection<int, TopicEntity> $topicsWithPoll
     */
    #[OneToMany(targetEntity: TopicEntity::class, mappedBy: 'poll')]
    public Collection $topicsWithPoll;


    public function __construct()
    {
        $this->uuid = Uuid::uuid4();

        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;

        $this->votes = new ArrayCollection();
        $this->answers = new ArrayCollection();
        $this->topicsWithPoll = new ArrayCollection();
    }

}