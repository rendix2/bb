<?php

namespace App\Model\Entity;

use App\Model\Repository\PollRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Ramsey\Uuid\Doctrine\UuidType;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[Entity()]
#[Table(name: 'poll_vote')]
class PollVoteEntity
{
    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT)]
    public string $id;

    #[Column(type: UuidType::NAME, unique: true)]
    public UuidInterface $uuid;

    #[ManyToOne(targetEntity: UserEntity::class, inversedBy: 'pollVotes')]
    #[JoinColumn(nullable: false)]
    public UserEntity $user;

    #[ManyToOne(targetEntity: CategoryEntity::class, inversedBy: 'pollVotes')]
    #[JoinColumn(nullable: false)]
    public CategoryEntity $category;

    #[ManyToOne(targetEntity: ForumEntity::class, inversedBy: 'pollVotes')]
    #[JoinColumn(nullable: false)]
    public ForumEntity $forum;

    #[ManyToOne(targetEntity: TopicEntity::class, inversedBy: 'pollVotes')]
    #[JoinColumn(nullable: false)]
    public TopicEntity $topic;

    #[ManyToOne(targetEntity: PollVoteEntity::class, inversedBy: 'votes')]
    #[JoinColumn(nullable: false)]
    public PollVoteEntity $poll;

    #[ManyToOne(targetEntity: PollAnswerEntity::class, inversedBy: 'votes')]
    #[JoinColumn(nullable: false)]
    public PollAnswerEntity $pollAnswer;

    #[Column(type: Types::DATETIME_IMMUTABLE)]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    public ?DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();

        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;
    }
}