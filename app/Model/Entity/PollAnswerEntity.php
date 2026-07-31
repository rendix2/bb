<?php

namespace App\Model\Entity;

use App\Model\Repository\PollRepository;
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
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;


#[Entity()]
#[Table(name: 'poll_answer')]
class PollAnswerEntity
{

    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT)]
    public string $id;

    #[Column(type: UuidType::NAME, unique: true)]
    public UuidInterface $uuid;

    #[ManyToOne(targetEntity: UserEntity::class, inversedBy: 'pollAnswers')]
    #[JoinColumn(nullable: false)]
    public UserEntity $user;

    #[ManyToOne(targetEntity: CategoryEntity::class, inversedBy: 'pollAnswers')]
    #[JoinColumn(nullable: false)]
    public CategoryEntity $category;

    #[ManyToOne(targetEntity: ForumEntity::class, inversedBy: 'pollAnswers')]
    #[JoinColumn(nullable: false)]
    public ForumEntity $forum;

    #[ManyToOne(targetEntity: TopicEntity::class, inversedBy: 'pollAnswers')]
    #[JoinColumn(nullable: false)]
    public TopicEntity $topic;

    #[ManyToOne(targetEntity: PollEntity::class, inversedBy: 'answers')]
    #[JoinColumn(nullable: false)]
    public PollEntity $poll;

    #[Column(type: Types::TEXT, nullable: false)]
    public string $text;

    #[Column(type: Types::DATETIME_IMMUTABLE)]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    public ?DateTimeImmutable $updatedAt;

    /**
     * @var Collection<int, PollVoteEntity> $votes
     */
    #[OneToMany(targetEntity: PollVoteEntity::class, mappedBy: 'pollAnswer', cascade: ['persist', 'remove'])]
    public Collection $votes;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();

        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;

        $this->votes = new ArrayCollection();
    }

}