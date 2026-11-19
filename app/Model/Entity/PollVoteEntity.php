<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping\UniqueConstraint;
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
#[Table(name: 'poll_vote', options: ['comment' => 'Poll vote'])]
// one vote per user and poll
#[UniqueConstraint(name: 'UK__Poll_vote__Poll_id_user_id', columns: ['poll_id', 'user_id'])]
class PollVoteEntity
{
    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT, options: ['comment' => 'ID'])]
    public int $id;

    #[Column(type: UuidType::NAME, unique: true, options: ['comment' => 'UUID'])]
    public UuidInterface $uuid;

    #[ManyToOne(targetEntity: UserEntity::class, inversedBy: 'pollVotes', cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'User ID'])]
    public UserEntity $user;

    #[ManyToOne(targetEntity: CategoryEntity::class, inversedBy: 'pollVotes', cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'Category ID'])]
    public CategoryEntity $category;

    #[ManyToOne(targetEntity: ForumEntity::class, inversedBy: 'pollVotes', cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'Forum ID'])]
    public ForumEntity $forum;

    #[ManyToOne(targetEntity: TopicEntity::class, inversedBy: 'pollVotes', cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'Topic ID'])]
    public TopicEntity $topic;

    #[ManyToOne(targetEntity: PollEntity::class, inversedBy: 'votes', cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'Poll ID'])]
    public PollEntity $poll;

    #[ManyToOne(targetEntity: PollAnswerEntity::class, inversedBy: 'votes', cascade: ['persist'])]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'Poll answer ID'])]
    public PollAnswerEntity $pollAnswer;

    #[Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => 'Created at'])]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => 'Updated at'])]
    public ?DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();

        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;
    }
}